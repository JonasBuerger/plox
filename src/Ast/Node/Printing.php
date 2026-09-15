<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;

class Printing extends Statement
{
    public function __construct(
        public Expression $expression,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPrintingStatement($this);
    }
}
