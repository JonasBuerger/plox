<?php

namespace Plox\Ast\Visitor;

use Plox\Ast\Expression as Expr;
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
use Plox\Ast\Node\PloxFunction;
use Plox\Ast\Node\PloxIf;
use Plox\Ast\Node\PloxPrint;
use Plox\Ast\Node\PloxReturn;
use Plox\Ast\Node\PloxVar;
use Plox\Ast\Node\PloxWhile;
use Plox\Ast\Node\Unary;
use Plox\Ast\Node\Variable;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Plox;
use Plox\Token;
use SplStack;

/**
 * @template-implements ExpressionVisitor<void>
 * @template-implements StatementVisitor<void>
 */
class Resolver implements ExpressionVisitor, StatementVisitor
{
    private FunctionType $currentFunction = FunctionType::NONE;
    private LoopType $currentLoop = LoopType::NONE;

    public function __construct(
        private readonly Interpreter $interpreter,
        /**
         * @var SplStack<array<string,bool>>
         */
        private readonly SplStack $scopes = new SplStack(),
    ) {
    }

    public function visitBinaryExpression(Binary $binary): void
    {
        $this->resolve($binary->left);
        $this->resolve($binary->right);
    }

    public function visitGroupingExpression(Grouping $grouping): void
    {
        $this->resolve($grouping->expression);
    }

    public function visitLiteralExpression(Literal $literal): void
    {
    }

    public function visitUnaryExpression(Unary $unary): void
    {
        $this->resolve($unary->right);
    }

    public function visitVariableExpression(Variable $variable): void
    {
        if (!$this->scopes->isEmpty() && ($this->scopes->top()[$variable->name->lexeme] ?? null) === false) {
            Plox::error($variable->name, "Can't read local variable in its own initializer.");
        }
        $this->resolveLocal($variable, $variable->name);
    }

    public function visitAssignExpression(Assign $assign): void
    {
        $this->resolve($assign->value);
        $this->resolveLocal($assign, $assign->name);
    }

    public function visitLogicalExpression(Logical $logical): void
    {
        $this->resolve($logical->left);
        $this->resolve($logical->right);
    }

    public function visitCallExpression(Call $call): void
    {
        $this->resolve($call->callee);
        foreach ($call->arguments as $argument) {
            $this->resolve($argument);
        }
    }

    public function visitExpressionStatement(Expression $expression): void
    {
        $this->resolve($expression->expression);
    }

    public function visitPloxPrintStatement(PloxPrint $ploxPrint): void
    {
        $this->resolve($ploxPrint->expression);
    }

    public function visitPloxVarStatement(PloxVar $ploxVar): void
    {
        $this->declare($ploxVar->name);
        if ($ploxVar->initializer instanceof Expr) {
            $this->resolve($ploxVar->initializer);
        }
        $this->define($ploxVar->name);
    }

    public function visitBlockStatement(Block $block): void
    {
        $this->beginScope();
        $this->resolveStatements($block->statements);
        $this->endScope();
    }

    /**
     * @param list<Statement> $statements
     */
    public function resolveStatements(array $statements): void
    {
        foreach ($statements as $statement) {
            $this->resolve($statement);
        }
    }

    public function visitPloxIfStatement(PloxIf $ploxIf): void
    {
        $this->resolve($ploxIf->condition);
        $this->resolve($ploxIf->thenBranch);
        if ($ploxIf->elseBranch instanceof Statement) {
            $this->resolve($ploxIf->elseBranch);
        }
    }

    public function visitPloxWhileStatement(PloxWhile $ploxWhile): void
    {
        $this->resolve($ploxWhile->condition);
        $enclosingLoop = $this->currentLoop;
        $this->currentLoop = LoopType::WHILE;
        $this->resolve($ploxWhile->body);
        $this->currentLoop = $enclosingLoop;
    }

    public function visitPloxBreakStatement(PloxBreak $ploxBreak): void
    {
        if ($this->currentLoop === LoopType::NONE) {
            Plox::error($ploxBreak->keyword, "Can't break outside of a loop.");
        }
    }

    public function visitPloxFunctionStatement(PloxFunction $ploxFunction): void
    {
        $this->declare($ploxFunction->name);
        $this->define($ploxFunction->name);

        $this->resolveFunction($ploxFunction, FunctionType::FUNCTION);
    }

    public function visitPloxReturnStatement(PloxReturn $ploxReturn): void
    {
        if ($this->currentFunction === FunctionType::NONE) {
            Plox::error($ploxReturn->keyword, "Can't return from top-level code.");
        }
        if ($ploxReturn->value instanceof Expr) {
            $this->resolve($ploxReturn->value);
        }
    }

    private function resolveLocal(Expr $expression, Token $name): void
    {
        for ($i = 0; $i < $this->scopes->count(); ++$i) {
            if (array_key_exists($name->lexeme, $this->scopes->offsetGet($i))) {
                $this->interpreter->resolve($expression, $i);
            }
        }
    }

    private function declare(Token $name): void
    {
        if ($this->scopes->isEmpty()) {
            return;
        }
        if (array_key_exists($name->lexeme, $this->scopes->top())) {
            Plox::error($name, 'Already a variable with this name in this scope.');
        }
        $scope = $this->scopes->pop();
        $scope[$name->lexeme] = false;
        $this->scopes->push($scope);
    }

    private function define(Token $name): void
    {
        if ($this->scopes->isEmpty()) {
            return;
        }
        $scope = $this->scopes->pop();
        $scope[$name->lexeme] = true;
        $this->scopes->push($scope);
    }

    private function resolve(Statement|Expr $statement): void
    {
        $statement->accept($this);
    }

    private function beginScope(): void
    {
        $this->scopes->push([]);
    }

    private function endScope(): void
    {
        $this->scopes->pop();
    }

    private function resolveFunction(PloxFunction $ploxFunction, FunctionType $type): void
    {
        $enclosingFunction = $this->currentFunction;
        $this->currentFunction = $type;
        $this->beginScope();
        foreach ($ploxFunction->params as $param) {
            $this->declare($param);
            $this->define($param);
        }
        $this->resolveStatements($ploxFunction->body);
        $this->endScope();
        $this->currentFunction = $enclosingFunction;
    }
}

enum FunctionType
{
    case NONE;
    case FUNCTION;
}
enum LoopType
{
    case NONE;
    case WHILE;
}
