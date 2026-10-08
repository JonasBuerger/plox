<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Visitor\Interpreter;

class RuntimeClass implements PloxCallable
{
    public function __construct(
        public readonly string $name,
        /**
         * @var list<RuntimeFunction>
         */
        public readonly array $methods,
    ) {
    }

    public function __toString(): string
    {
        return "<class '{$this->name}'>";
    }

    public function arity(): int
    {
        if (array_key_exists('init', $this->methods)) {
            return $this->methods['init']->arity();
        }

        return 0;
    }

    public function call(Interpreter $interpreter, array $arguments): mixed
    {
        $instance = new Instance($this);
        if (array_key_exists('init', $this->methods)) {
            $initializer = $this->methods['init']->bind($instance);
            $initializer->call($interpreter, $arguments);
        }

        return $instance;
    }
}
