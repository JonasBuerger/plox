<?php

namespace Plox;

class Unary extends Expression
{
    public function __construct(
        public Token $operator,
        public Expression $inner,
    )
    {
    }
}
