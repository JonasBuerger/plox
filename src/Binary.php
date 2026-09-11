<?php

namespace Plox;

class Binary extends Expression
{
    public function __construct(
        public Expression $left,
        public Token $operator,
        public Expression $right,
    )
    {
    }
}
