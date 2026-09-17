<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;

class PloxWhile extends Statement
{
    public function __construct(
        public Expression $condition,
        public Statement $body,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPloxWhileStatement($this);
    }
}
