<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;

class Block extends Statement
{
    public function __construct(
        public array $statements,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitBlockStatement($this);
    }
}
