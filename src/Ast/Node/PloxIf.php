<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;

class PloxIf extends Statement
{
    public function __construct(
        public Expression $condition,
        public Statement $thenBranch,
        public ?Statement $elseBranch,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPloxIfStatement($this);
    }
}
