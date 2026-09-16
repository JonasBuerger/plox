<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Token;

class Unary extends Expression
{
    public function __construct(
        public Token $operator,
        public Expression $right,
    ) {
    }

    public function accept(ExpressionVisitor $visitor): mixed
    {
        return $visitor->visitUnaryExpression($this);
    }
}
