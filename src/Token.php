<?php

namespace Plox;

class Token implements \Stringable
{
    public function __construct(
        public TokenType         $type,
        public string            $lexeme,
        public string|float|null $literal,
        public int               $line,
    )
    {
    }

    public function __toString(): string
    {
        return $this->type->name . ' ' . $this->lexeme . ' ' . $this->literal;
    }
}
