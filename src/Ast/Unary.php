<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Expression;
use Plox\Token;

class Unary extends Expression
{
    public function __construct(public Token $operator,
        public Expression $right,
    ) {
    }
}
