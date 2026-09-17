<?php

namespace Plox\Ast\Visitor;

use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Assign;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Block;
use Plox\Ast\Node\Call;
use Plox\Ast\Node\Expression;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Logical;
use Plox\Ast\Node\PloxBreak;
use Plox\Ast\Node\PloxIf;
use Plox\Ast\Node\PloxPrint;
use Plox\Ast\Node\PloxVar;
use Plox\Ast\Node\PloxWhile;
use Plox\Ast\Node\Unary;
use Plox\Ast\Node\Variable;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Environment;
use Plox\Plox;
use Plox\PloxCallable;
use Plox\RuntimeException;
use Plox\Token;
use Plox\TokenType;

/**
 * @template-implements ExpressionVisitor<mixed>
 * @template-implements StatementVisitor<void>
 */
class Interpreter implements ExpressionVisitor, StatementVisitor
{
    private ?Token $breakTraversal = null;
    public readonly Environment $globals;
    private Environment $environment;

    public function __construct()
    {
        $this->globals = new Environment();
        $this->environment = new Environment($this->globals);

        $this->globals->define('clock', new class implements PloxCallable {
            public function call(Interpreter $interpreter, array $arguments): mixed
            {
                return microtime(true);
            }

            public function arity(): int
            {
                return 0;
            }

            public function __toString(): string
            {
                return '<native fn>';
            }
        });
    }

    /**
     * @param list<Statement> $statements
     */
    public function interpret(array $statements): void
    {
        try {
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
            if ($this->breakTraversal instanceof Token) {
                throw new RuntimeException($this->breakTraversal, 'break must be within a loop.');
            }
        } catch (RuntimeException $e) {
            Plox::error($e->getToken(), $e->getMessage());
        }
    }

    public function visitBinaryExpression(Binary $binary): mixed
    {
        $left = $this->evaluate($binary->left);
        $right = $this->evaluate($binary->right);

        switch ($binary->operator->type) {
            case TokenType::MINUS:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left - $right;
            case TokenType::SLASH:
                $this->checkNumberOperands($binary->operator, $left, $right);
                if ($right === 0.0) {
                    throw new RuntimeException($binary->operator, 'Division by zero.');
                }

                return $left / $right;
            case TokenType::STAR:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left * $right;
            case TokenType::PLUS:
                return $this->plus($binary->operator, $left, $right);
            case TokenType::GREATER:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left > $right;
            case TokenType::GREATER_EQUAL:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left >= $right;
            case TokenType::LESS:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left < $right;
            case TokenType::LESS_EQUAL:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left <= $right;
            case TokenType::BANG_EQUAL:
                return $left !== $right;
            case TokenType::EQUAL_EQUAL:
                return $left === $right;
            default:
                throw new RuntimeException($binary->operator, 'Not implemented.');
        }
    }

    public function visitGroupingExpression(Grouping $grouping): mixed
    {
        return $this->evaluate($grouping->expression);
    }

    public function visitLiteralExpression(Literal $literal): mixed
    {
        return $literal->value;
    }

    public function visitUnaryExpression(Unary $unary): string|float|bool
    {
        $value = $this->evaluate($unary->right);

        switch ($unary->operator->type) {
            case TokenType::BANG:
                return $value === false || $value === null;
            case TokenType::MINUS:
                $this->checkNumberOperands($unary->operator, $value);

                return -$value;
            default:
                throw new RuntimeException($unary->operator, 'Not implemented.');
        }
    }

    private function plus(Token $operator, mixed $left, mixed $right): string|float
    {
        if (is_float($left) && is_float($right)) {
            return $left + $right;
        }
        if (is_string($left) && is_string($right)) {
            return $left . $right;
        }

        throw new RuntimeException($operator, 'Operands must be two numbers or two strings.');
    }

    private function checkNumberOperands(Token $operator, mixed ...$operands): void
    {
        if (array_all($operands, fn (mixed $value, $_key): bool => is_float($value))) {
            return;
        }
        throw new RuntimeException($operator, 'Operand must be a number.');
    }

    private function stringify(mixed $value): string
    {
        return $value === null ? 'nil' : strval($value);
    }

    public function visitExpressionStatement(Expression $expression): void
    {
        $this->evaluate($expression->expression);
    }

    public function visitPloxPrintStatement(PloxPrint $ploxPrint): void
    {
        $value = $this->evaluate($ploxPrint->expression);
        echo $this->stringify($value), PHP_EOL;
    }

    public function visitVariableExpression(Variable $variable): mixed
    {
        return $this->environment->get($variable->name);
    }

    public function visitPloxVarStatement(PloxVar $ploxVar): void
    {
        $value = null;
        if ($ploxVar->initializer instanceof \Plox\Ast\Expression) {
            $value = $this->evaluate($ploxVar->initializer);
        }
        $this->environment->define($ploxVar->name->lexeme, $value);
    }

    public function visitAssignExpression(Assign $assign): mixed
    {
        $value = $this->evaluate($assign->value);
        $this->environment->assign($assign->name, $value);

        return $value;
    }

    public function visitBlockStatement(Block $block): void
    {
        $outerEnvironment = $this->environment;
        $this->environment = new Environment($outerEnvironment);
        foreach ($block->statements as $statement) {
            $this->execute($statement);
        }
        $this->environment = $outerEnvironment;
    }

    private function isTruthy(mixed $value): bool
    {
        return (bool) ($value ?? false);
    }

    public function visitPloxIfStatement(PloxIf $ploxIf): void
    {
        $condition = $this->evaluate($ploxIf->condition);
        if ($this->isTruthy($condition)) {
            $this->execute($ploxIf->thenBranch);
        } elseif ($ploxIf->elseBranch instanceof Statement) {
            $this->execute($ploxIf->elseBranch);
        }
    }

    public function visitPloxWhileStatement(PloxWhile $ploxWhile): void
    {
        while ($this->isTruthy($this->evaluate($ploxWhile->condition))) {
            $this->execute($ploxWhile->body);
            if ($this->breakTraversal instanceof Token) {
                break;
            }
        }
        $this->breakTraversal = null;
    }

    public function visitLogicalExpression(Logical $logical): mixed
    {
        $left = $this->evaluate($logical->left);

        if ($logical->operator->type === TokenType::OR) {
            if ($this->isTruthy($left)) {
                return $left;
            }
        } else {
            if (!$this->isTruthy($left)) {
                return $left;
            }
        }

        return $this->evaluate($logical->right);
    }

    public function visitPloxBreakStatement(PloxBreak $ploxBreak): void
    {
        $this->breakTraversal = $ploxBreak->break;
    }

    private function execute(Statement $statement): void
    {
        if ($this->breakTraversal instanceof Token) {
            return;
        }
        $statement->accept($this);
    }

    private function evaluate(\Plox\Ast\Expression $expression): mixed
    {
        return $expression->accept($this);
    }

    public function visitCallExpression(Call $call): mixed
    {
        $callee = $this->evaluate($call->callee);
        $arguments = [];
        foreach ($call->arguments as $argument) {
            $arguments[] = $this->evaluate($argument);
        }
        if ($callee instanceof PloxCallable) {
            $argumentCount = count($arguments);
            if ($argumentCount !== $callee->arity()) {
                throw new RuntimeException($call->paren, "Expected {$callee->arity()} arguments but got $argumentCount.");
            }

            return $callee->call($this, $arguments);
        }
        throw new RuntimeException($call->paren, 'Can only call functions and classes.');
    }
}
