<?php

declare(strict_types=1);

namespace Plox;

/**
 * @phpstan-type VariableTypes string|float|bool|null
 */
final class Environment
{
    /**
     * @var array<string, VariableTypes>
     */
    private array $values = [];

    public function __construct(
        private readonly ?Environment $enclosing = null,
    ) {
    }

    /**
     * @return VariableTypes
     */
    public function get(Token $name): mixed
    {
        if ($this->has($name->lexeme)) {
            return $this->values[$name->lexeme];
        }

        if ($this->enclosing instanceof \Plox\Environment) {
            return $this->enclosing->get($name);
        }

        throw new RuntimeException($name, "Undefined variable '" . $name->lexeme . "'.");
    }

    /**
     * @param VariableTypes $value
     */
    public function define(Token $name, mixed $value): void
    {
        $this->values[$name->lexeme] = $value;
    }

    /**
     * @param VariableTypes $value
     */
    public function assign(Token $name, mixed $value): void
    {
        if ($this->has($name->lexeme)) {
            $this->values[$name->lexeme] = $value;
            return;
        }
        if ($this->enclosing instanceof \Plox\Environment) {
            $this->enclosing->assign($name, $value);
            return;
        }

        throw new RuntimeException($name, "Undefined variable '" . $name->lexeme . "'.");
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }
}
