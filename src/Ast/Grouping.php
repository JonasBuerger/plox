<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Expression;

class Grouping extends Expression
{
    public function __construct(public Expression $expression,
    ) {
    }
}
