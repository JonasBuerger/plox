<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Expression;

class Literal extends Expression
{
    public function __construct(public object $value,
    ) {
    }
}
