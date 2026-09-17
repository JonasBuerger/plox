<?php

namespace Plox\Ast\Visitor;

use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Assign;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Block;
use Plox\Ast\Node\Expression;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Logical;
use Plox\Ast\Node\PloxIf;
use Plox\Ast\Node\PloxPrint;
use Plox\Ast\Node\PloxVar;
use Plox\Ast\Node\Unary;
use Plox\Ast\Node\Variable;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Environment;
use Plox\Plox;
use Plox\RuntimeException;
use Plox\Token;
use Plox\TokenType;

/**
 * @template-implements ExpressionVisitor<string|float|bool|null>
 * @template-implements StatementVisitor<void>
 */
class Interpreter implements ExpressionVisitor, StatementVisitor
{
    public function __construct(
        private Environment $environment = new Environment(),
    ) {
    }

    /**
     * @param list<Statement> $statements
     */
    public function interpret(array $statements): void
    {
        try {
            foreach ($statements as $statement) {
                $statement->accept($this);
            }
        } catch (RuntimeException $e) {
            Plox::error($e->getToken(), $e->getMessage());
        }
    }

    public function visitBinaryExpression(Binary $binary): string|float|bool|null
    {
        $left = $binary->left->accept($this);
        $right = $binary->right->accept($this);

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

    public function visitGroupingExpression(Grouping $grouping): string|float|bool|null
    {
        return $grouping->expression->accept($this);
    }

    public function visitLiteralExpression(Literal $literal): string|float|bool|null
    {
        return $literal->value;
    }

    public function visitUnaryExpression(Unary $unary): string|float|bool
    {
        $value = $unary->right->accept($this);

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

    private function plus(Token $operator, string|float|bool|null $left, string|float|bool|null $right): string|float
    {
        if (is_float($left) && is_float($right)) {
            return $left + $right;
        }
        if (is_string($left) && is_string($right)) {
            return $left . $right;
        }

        throw new RuntimeException($operator, 'Operands must be two numbers or two strings.');
    }

    private function checkNumberOperands(Token $operator, string|float|bool|null ...$operands): void
    {
        if (array_all($operands, fn (string|float|bool|null $value, $_key): bool => is_float($value))) {
            return;
        }
        throw new RuntimeException($operator, 'Operand must be a number.');
    }

    private function stringify(string|float|bool|null $value): string
    {
        return $value === null ? 'nil' : strval($value);
    }

    public function visitExpressionStatement(Expression $expression): void
    {
        $expression->expression->accept($this);
    }

    public function visitPloxPrintStatement(PloxPrint $ploxPrint): void
    {
        $value = $ploxPrint->expression->accept($this);
        echo $this->stringify($value), PHP_EOL;
    }

    public function visitVariableExpression(Variable $variable): string|float|bool|null
    {
        return $this->environment->get($variable->name);
    }

    public function visitPloxVarStatement(PloxVar $ploxVar): void
    {
        $value = $ploxVar->initializer?->accept($this);
        $this->environment->define($ploxVar->name, $value);
    }

    public function visitAssignExpression(Assign $assign): string|float|bool|null
    {
        $value = $assign->value->accept($this);
        $this->environment->assign($assign->name, $value);

        return $value;
    }

    public function visitBlockStatement(Block $block): void
    {
        $outerEnvironment = $this->environment;
        $this->environment = new Environment($outerEnvironment);
        foreach ($block->statements as $statement) {
            $statement->accept($this);
        }
        $this->environment = $outerEnvironment;
    }

    private function isTruthy($value): bool
    {
        return (bool) ($value ?? false);
    }

    public function visitPloxIfStatement(PloxIf $ploxIf): void
    {
        if ($this->isTruthy($ploxIf->condition->accept($this))) {
            $ploxIf->thenBranch->accept($this);
        } else {
            $ploxIf->elseBranch?->accept($this);
        }
    }

    public function visitLogicalExpression(Logical $logical)
    {
        $left = $logical->left->accept($this);

        if ($logical->operator->type === TokenType::OR) {
            if ($this->isTruthy($left)) {
                return $left;
            }
        } else {
            if (!$this->isTruthy($left)) {
                return $left;
            }
        }

        return $logical->right->accept($this);
    }
}
