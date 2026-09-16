<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Token;

class Assign extends Expression
{
    public function __construct(
        public Token $name,
        public Expression $value,
    ) {
    }

    public function accept(ExpressionVisitor $visitor): mixed
    {
        return $visitor->visitAssignExpression($this);
    }
}
