<?php

declare(strict_types=1);

namespace Plox;

use Stringable;

class Instance implements Stringable
{
    public function __construct(private readonly RuntimeClass $class)
    {
    }

    public function __toString(): string
    {
        return "<Instance of {$this->class->name}>";
    }
}
