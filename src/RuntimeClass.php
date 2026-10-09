<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Visitor\Interpreter;

readonly class RuntimeClass implements PloxCallable
{
    public function __construct(
        public string $name,
        public ?RuntimeClass $superclass,
        /**
         * @var list<RuntimeFunction>
         */
        private array $methods,
    ) {
    }

    public function __toString(): string
    {
        return "<class '{$this->name}'>";
    }

    public function arity(): int
    {
        return $this->findMethod('init')?->arity() ?? 0;
    }

    public function findMethod(string $name): ?RuntimeFunction
    {
        if (array_key_exists($name, $this->methods)) {
            return $this->methods[$name];
        }

        return $this->superclass?->findMethod($name);
    }

    public function call(Interpreter $interpreter, array $arguments): mixed
    {
        $instance = new Instance($this);
        if (($initializer = $this->findMethod('init')) instanceof RuntimeFunction) {
            $initializer->bind($instance)->call($interpreter, $arguments);
        }

        return $instance;
    }
}
