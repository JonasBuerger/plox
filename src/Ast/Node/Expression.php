<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;

class Expression extends Statement
{
    public function __construct(
        public \Plox\Ast\Expression $expression,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitExpressionStatement($this);
    }
}
