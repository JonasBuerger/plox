<?php

namespace Plox;

class Grouping extends Expression
{
    public function __construct(
        public Token $left,
        public Expression $inner,
        public Token $right,
    )
    {
    }
}
