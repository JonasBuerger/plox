<?php

namespace Plox;

use Plox\Ast\Node;
use Plox\Ast\Visitor\Interpreter;

class PloxFunction implements PloxCallable
{
    public function __construct(
        private readonly Node\PloxFunction $declaration,
        private readonly Environment $closure,
    ) {
    }

    public function __toString(): string
    {
        return "<fun '{$this->declaration->name->lexeme}'>";
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
        } catch (PloxReturn $return) {
            return $return->getValue();
        }

        return null;
    }

    public function arity(): int
    {
        return count($this->declaration->params);
    }
}
