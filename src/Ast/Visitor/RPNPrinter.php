<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;

/**
 * @template-implements ExpressionVisitor<string>
 */
class RPNPrinter implements ExpressionVisitor
{
    public function visitBinary(Binary $binary): mixed
    {
        return $binary->left->accept($this) . ' ' . $binary->right->accept($this) . ' ' . $binary->operator->lexeme;
    }

    public function visitGrouping(Grouping $grouping): mixed
    {
        return $grouping->expression->accept($this);
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
        return $unary->right->accept($this) . ' #' . $unary->operator->lexeme;
    }
}
