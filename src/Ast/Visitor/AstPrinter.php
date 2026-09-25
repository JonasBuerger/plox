<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Ast\Node\Expr as Expression;

/**
 * @template-implements ExprVisitor<string>
 */
class AstPrinter implements ExprVisitor
{
    public function visitBinaryExpr(Expression\Binary $binary): string
    {
        return $this->parenthesize($binary->operator->lexeme, $binary->left, $binary->right);
    }

    public function visitGroupingExpr(Expression\Grouping $grouping): string
    {
        return $this->parenthesize('group', $grouping->expression);
    }

    public function visitLiteralExpr(Expression\Literal $literal): string
    {
        return match (gettype($literal->value)) {
            'boolean' => $literal->value ? 'true' : 'false',
            'integer', 'double' => strval($literal->value),
            'string' => '"' . $literal->value . '"',
            'NULL' => 'nil',
            default => '',
        };
    }

    public function visitUnaryExpr(Expression\Unary $unary): string
    {
        return $this->parenthesize($unary->operator->lexeme, $unary->right);
    }

    public function visitVariableExpr(Expression\Variable $variable): string
    {
        return $this->parenthesize('get ' . $variable->name->lexeme);
    }

    public function visitAssignExpr(Expression\Assign $assign): string
    {
        return $this->parenthesize('assign ' . $assign->name->lexeme, $assign->value->accept($this));
    }

    public function visitLogicalExpr(Expression\Logical $logical): string
    {
        return $this->parenthesize($logical->operator->lexeme, $logical->left, $logical->right);
    }

    public function visitCallExpr(Expression\Call $call): string
    {
        return $this->parenthesize('call', $call->callee->accept($this), ...$call->arguments);
    }

    private function parenthesize(string $name, Expr ...$expressions): string
    {
        return '(' . $name . ' ' . implode(' ', array_map(fn (Expr $expression): mixed => $expression->accept($this), $expressions)) . ')';
    }
}
