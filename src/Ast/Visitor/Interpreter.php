<?php

namespace Plox\Ast\Visitor;

use http\Exception\RuntimeException;
use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;
use Plox\Plox;
use Plox\TokenType;

/**
 * @template-implements ExpressionVisitor<bool|int|float|string|null>
 */
class Interpreter implements ExpressionVisitor
{

    /**
     * @inheritDoc
     */
    public function visitBinary(Binary $binary): bool|int|float|string|null
    {
        $left = $binary->left->accept($this);
        $right = $binary->right->accept($this);
        return match ($binary->operator->type) {
            TokenType::MINUS => floatval($left) - floatval($right),
            TokenType::SLASH => floatval($left) / floatval($right),
            TokenType::STAR => floatval($left) * floatval($right),
            TokenType::PLUS => $this->plus($left, $right),
            default => null,
        };
    }

    /**
     * @inheritDoc
     */
    public function visitGrouping(Grouping $grouping): bool|int|float|string|null
    {
        return $grouping->expression->accept($this);
    }

    /**
     * @inheritDoc
     */
    public function visitLiteral(Literal $literal): bool|int|float|string|null
    {
        return $literal->value;
    }

    /**
     * @inheritDoc
     */
    public function visitUnary(Unary $unary): bool|int|float|string|null
    {
        $value = $unary->right->accept($this);
        return match ($unary->operator->type) {
            //Chapter 7: false and nil are falsey, everything else is truthy
            TokenType::BANG => $value === false || $value === null,
            TokenType::MINUS => $this->isNumber($value) ? -$value : null,
            default => null,
        };
    }

    private function isNumber(bool|int|float|string|null $value): bool
    {
        return is_int($value) || is_float($value);
    }


    private function plus(bool|int|float|string|null $left, bool|int|float|string|null $right): float|string|null
    {
        if ($this->isNumber($left) && $this->isNumber($right)) {
            return floatval($left) + floatval($right);
        }
        if (is_string($left) && is_string($right)) {
            return $left . $right;
        }
        return null;
    }
}
