<?php

namespace Plox;

use Plox\Ast\Node\Stmt\PloxFunction;
use Plox\Ast\Visitor\Interpreter;

readonly class RuntimeFunction implements PloxCallable
{
    public function __construct(
        private PloxFunction $declaration,
        private Environment $closure,
        private bool $isInitializer,
    ) {
    }

    public function __toString(): string
    {
        return "<fun '{$this->declaration->name->lexeme}'>";
    }

    public function arity(): int
    {
        return count($this->declaration->params);
    }

    public function call(Interpreter $interpreter, array $arguments): mixed
    {
        $environment = new Environment($this->closure);
        $counter = count($this->declaration->params);
        for ($i = 0; $i < $counter; ++$i) {
            $environment->define($this->declaration->params[$i]->lexeme, $arguments[$i]);
        }

        try {
            $interpreter->executeBlock($this->declaration->body, $environment);
        } catch (ReturnThrowable $return) {
            if ($this->isInitializer) {
                return $this->closure->getAt(0, 'this');
            }

            return $return->getValue();
        }

        if ($this->isInitializer) {
            return $this->closure->getAt(0, 'this');
        }

        return null;
    }

    public function bind(Instance $instance): self
    {
        $environment = new Environment($this->closure);
        $environment->define('this', $instance);

        return new RuntimeFunction($this->declaration, $environment, $this->isInitializer);
    }
}
