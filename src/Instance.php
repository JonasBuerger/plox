<?php

declare(strict_types=1);

namespace Plox;

use Stringable;

class Instance implements Stringable
{
    private array $fields = [];

    public function __construct(private readonly RuntimeClass $class)
    {
    }

    public function __toString(): string
    {
        return "<Instance of {$this->class->name}>";
    }

    public function get(Token $name): mixed
    {
        if (array_key_exists($name->lexeme, $this->fields)) {
            return $this->fields[$name->lexeme];
        }
        if (($method = $this->class->findMethod($name->lexeme)) instanceof RuntimeFunction) {
            return $method->bind($this);
        }

        throw new RuntimeException($name, "Undefined property '" . $name->lexeme . "'.");
    }

    public function set(Token $name, mixed $value): void
    {
        $this->fields[$name->lexeme] = $value;
    }
}
