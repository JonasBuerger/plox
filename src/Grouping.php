<?php

declare(strict_types=1);

namespace Plox;

class Grouping extends Expression
{
    public function __construct(
        public Token $left,
        public Expression $inner,
        public Token $right,
    ) {
    }
}
