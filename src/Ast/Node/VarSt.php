<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Statement;
use Plox\Token;
use Plox\Ast\Expression;
use Plox\Ast\StatementVisitor;

class VarSt extends Statement
{
    public function __construct(
        public Token $name,
        public ?Expression $initializer,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitVarStStatement($this);
    }
}
