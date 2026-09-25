<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

use Plox\Ast\ExprVisitor;
use Plox\Ast\Node\Expr as Expression;

/**
 * @template-implements ExprVisitor<string>
 */
class RPNPrinter implements ExprVisitor
{
    public function visitBinaryExpr(Expression\Binary $binary): string
    {
        return $binary->left->accept($this) . ' ' . $binary->right->accept($this) . ' ' . $binary->operator->lexeme;
    }

    public function visitGroupingExpr(Expression\Grouping $grouping): string
    {
        return $grouping->expression->accept($this);
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
        return $unary->right->accept($this) . ' #' . $unary->operator->lexeme;
    }

    public function visitVariableExpr(Expression\Variable $variable): string
    {
        return $variable->name . ' get';
    }

    public function visitAssignExpr(Expression\Assign $assign): string
    {
        return $assign->value->accept($this) . ' ' . $assign->name->lexeme . ' decl';
    }

    public function visitLogicalExpr(Expression\Logical $logical): string
    {
        return $logical->left->accept($this) . ' ' . $logical->right->accept($this) . ' ' . $logical->operator->lexeme;
    }

    public function visitCallExpr(Expression\Call $call): string
    {
        $rpn = $call->callee->accept($this) . ' ';
        foreach ($call->arguments as $arg) {
            $rpn .= ' ' . $arg->accept($this) . ' stack';
        }

        return $rpn . ' call';
    }
}
