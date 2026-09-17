<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Visitor\Interpreter;

interface PloxCallable extends \Stringable
{
    /**
     * @param list<mixed> $arguments
     */
    public function call(Interpreter $interpreter, array $arguments): mixed;

    public function arity(): int;
}
