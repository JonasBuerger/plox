<?php

declare(strict_types=1);

namespace Plox;

class Binary extends Expression
{
    public function __construct(
        public Expression $left,
        public Token $operator,
        public Expression $right,
    ) {
    }
}
