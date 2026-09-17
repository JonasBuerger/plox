<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Token;

class PloxBreak extends Statement
{
    public function __construct(
        public Token $break,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPloxBreakStatement($this);
    }
}
