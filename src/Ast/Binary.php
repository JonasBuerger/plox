<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Expression;
use Plox\Token;

class Binary extends Expression
{
    public function __construct(public Expression $left,
        public Token $operator,
        public Expression $right,
    ) {
    }
}
