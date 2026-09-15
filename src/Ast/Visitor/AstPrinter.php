<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;

/**
 * @template-implements ExpressionVisitor<string>
 */
class AstPrinter implements ExpressionVisitor
{
    public function visitBinaryExpression(Binary $binary): string
    {
        return $this->parenthesize($binary->operator->lexeme, $binary->left, $binary->right);
    }

    public function visitGroupingExpression(Grouping $grouping): string
    {
        return $this->parenthesize('group', $grouping->expression);
    }

    public function visitLiteralExpression(Literal $literal): string
    {
        return match (gettype($literal->value)) {
            'boolean' => $literal->value ? 'true' : 'false',
            'integer', 'double' => strval($literal->value),
            'string' => '"' . $literal->value . '"',
            'NULL' => 'nil',
            default => '',
        };
    }

    public function visitUnaryExpression(Unary $unary): string
    {
        return $this->parenthesize($unary->operator->lexeme, $unary->right);
    }

    private function parenthesize(string $name, Expression ...$expressions): string
    {
        return '(' . $name . ' ' . implode(' ', array_map(fn (Expression $expression): mixed => $expression->accept($this), $expressions)) . ')';
    }
}
