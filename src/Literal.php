<?php

declare(strict_types=1);

namespace Plox;

class Literal extends Expression
{
    public function __construct(
        public Token $token,
    ) {
    }
}
