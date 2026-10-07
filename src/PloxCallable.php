<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Visitor\Interpreter;
use Stringable;

interface PloxCallable extends Stringable
{
    public function arity(): int;

    /**
     * @param list<mixed> $arguments
     */
    public function call(Interpreter $interpreter, array $arguments): mixed;
}
