<?php

declare(strict_types=1);

namespace Plox;

use Stringable;

class Token implements Stringable
{
    public function __construct(
        public TokenType $type,
        public string $lexeme,
        public string|float|null $literal,
        public int $line,
    ) {
    }

    public function __toString(): string
    {
        return $this->type->name . ' ' . $this->lexeme . ' ' . $this->literal;
    }
}
