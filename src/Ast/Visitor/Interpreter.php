<?php

namespace Plox\Ast\Visitor;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Ast\Node\Expr as Expression;
use Plox\Ast\Node\Stmt as Statement;
use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;
use Plox\BreakThrowable;
use Plox\Environment;
use Plox\Plox;
use Plox\PloxCallable;
use Plox\ReturnThrowable;
use Plox\RuntimeClass;
use Plox\RuntimeException;
use Plox\RuntimeFunction;
use Plox\Token;
use Plox\TokenType;

/**
 * @template-implements ExprVisitor<mixed>
 * @template-implements StmtVisitor<void>
 */
class Interpreter implements ExprVisitor, StmtVisitor
{
    public readonly Environment $globals;
    /**
     * @var array<int, int>
     */
    private array $locals = [];
    private Environment $environment;

    public function __construct()
    {
        $this->globals = new Environment();
        $this->environment = $this->globals;

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
     * @param list<Stmt> $statements
     */
    public function interpret(array $statements): void
    {
        try {
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } catch (RuntimeException $e) {
            Plox::error($e->getToken(), $e->getMessage());
        } catch (ReturnThrowable $return) {
            Plox::error($return->getReturnToken(), 'return outside of a function.');
        } catch (BreakThrowable $break) {
            Plox::error($break->getBreakToken(), 'break must be within a loop.');
        }
    }

    public function visitBinaryExpr(Expression\Binary $binary): mixed
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

    public function visitGroupingExpr(Expression\Grouping $grouping): mixed
    {
        return $this->evaluate($grouping->expression);
    }

    public function visitLiteralExpr(Expression\Literal $literal): mixed
    {
        return $literal->value;
    }

    public function visitUnaryExpr(Expression\Unary $unary): string|float|bool
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

    public function visitExpressionStmt(Statement\Expression $expression): void
    {
        $this->evaluate($expression->expression);
    }

    public function visitPloxPrintStmt(Statement\PloxPrint $ploxPrint): void
    {
        $value = $this->evaluate($ploxPrint->expression);
        echo $this->stringify($value), PHP_EOL;
    }

    public function visitVariableExpr(Expression\Variable $variable): mixed
    {
        return $this->lookupVariable($variable->name, $variable);
    }

    public function visitPloxVarStmt(Statement\PloxVar $ploxVar): void
    {
        $value = null;
        if ($ploxVar->initializer instanceof Expr) {
            $value = $this->evaluate($ploxVar->initializer);
        }
        $this->environment->define($ploxVar->name->lexeme, $value);
    }

    public function visitAssignExpr(Expression\Assign $assign): mixed
    {
        $value = $this->evaluate($assign->value);
        $distance = $this->locals[spl_object_id($assign)] ?? null;
        if ($distance !== null) {
            $this->environment->assignAt($distance, $assign->name, $value);
        } else {
            $this->globals->assign($assign->name, $value);
        }

        return $value;
    }

    public function visitBlockStmt(Statement\Block $block): void
    {
        $this->executeBlock($block->statements, new Environment($this->environment));
    }

    /**
     * @param list<Stmt> $statements
     */
    public function executeBlock(array $statements, Environment $environment): void
    {
        $previousEnvironment = $this->environment;
        $this->environment = $environment;
        try {
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } finally {
            $this->environment = $previousEnvironment;
        }
    }

    public function visitPloxIfStmt(Statement\PloxIf $ploxIf): void
    {
        $condition = $this->evaluate($ploxIf->condition);
        if ($this->isTruthy($condition)) {
            $this->execute($ploxIf->thenBranch);
        } elseif ($ploxIf->elseBranch instanceof Stmt) {
            $this->execute($ploxIf->elseBranch);
        }
    }

    public function visitPloxWhileStmt(Statement\PloxWhile $ploxWhile): void
    {
        try {
            while ($this->isTruthy($this->evaluate($ploxWhile->condition))) {
                $this->execute($ploxWhile->body);
            }
        } catch (BreakThrowable) {
        }
    }

    public function visitLogicalExpr(Expression\Logical $logical): mixed
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

    public function visitPloxBreakStmt(Statement\PloxBreak $ploxBreak): void
    {
        throw new BreakThrowable($ploxBreak->keyword);
    }

    public function visitCallExpr(Expression\Call $call): mixed
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

    public function visitPloxFunctionStmt(Statement\PloxFunction $ploxFunction): void
    {
        $function = new RuntimeFunction($ploxFunction, $this->environment);
        $this->environment->define($ploxFunction->name->lexeme, $function);
    }

    public function visitPloxReturnStmt(Statement\PloxReturn $ploxReturn): never
    {
        $value = null;
        if ($ploxReturn->value instanceof Expr) {
            $value = $this->evaluate($ploxReturn->value);
        }
        throw new ReturnThrowable($ploxReturn->keyword, $value);
    }

    public function resolve(Expr $expression, int $depth): void
    {
        $this->locals[spl_object_id($expression)] = $depth;
    }

    public function visitPloxClassStmt(Statement\PloxClass $ploxClass): void
    {
        $this->environment->define($ploxClass->name->lexeme, null);
        $class = new RuntimeClass($ploxClass->name->lexeme);
        $this->environment->assign($ploxClass->name, $class);
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

    private function lookupVariable(Token $name, Expr $expression): mixed
    {
        $distance = $this->locals[spl_object_id($expression)] ?? null;
        if ($distance !== null) {
            return $this->environment->getAt($distance, $name->lexeme);
        }

        return $this->globals->get($name);
    }

    private function isTruthy(mixed $value): bool
    {
        return (bool) ($value ?? false);
    }

    private function execute(Stmt $statement): void
    {
        $statement->accept($this);
    }

    private function evaluate(Expr $expression): mixed
    {
        return $expression->accept($this);
    }
}
