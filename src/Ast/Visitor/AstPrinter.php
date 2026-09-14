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
    public function visitBinary(Binary $binary): mixed
    {
        return $this->parenthesize($binary->operator->lexeme, $binary->left, $binary->right);
    }

    public function visitGrouping(Grouping $grouping): mixed
    {
        return $this->parenthesize('group', $grouping->expression);
    }

    public function visitLiteral(Literal $literal): mixed
    {
        return match (gettype($literal->value)) {
            'boolean' => $literal->value ? 'true' : 'false',
            'integer', 'double' => strval($literal->value),
            'string' => '"' . $literal->value . '"',
            'NULL' => 'nil',
            default => '',
        };
    }

    public function visitUnary(Unary $unary): mixed
    {
        return $this->parenthesize($unary->operator->lexeme, $unary->right);
    }

    private function parenthesize(string $name, Expression ...$expressions): string
    {
        return '(' . $name . ' ' . implode(' ', array_map(fn (Expression $expression): mixed => $expression->accept($this), $expressions)) . ')';
    }
}
