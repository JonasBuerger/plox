<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Token;

class Variable extends Expression
{
    public function __construct(
        public Token $name,
    ) {
    }

    public function accept(ExpressionVisitor $visitor): mixed
    {
        return $visitor->visitVariableExpression($this);
    }
}
