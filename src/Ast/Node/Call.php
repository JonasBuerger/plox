<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Token;

class Call extends Expression
{
    public function __construct(
        public Expression $callee,
        public Token $paren,
        public array $arguments,
    ) {
    }

    public function accept(ExpressionVisitor $visitor): mixed
    {
        return $visitor->visitCallExpression($this);
    }
}
