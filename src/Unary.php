<?php

declare(strict_types=1);

namespace Plox;

class Unary extends Expression
{
    public function __construct(
        public Token $operator,
        public Expression $inner,
    ) {
    }
}
