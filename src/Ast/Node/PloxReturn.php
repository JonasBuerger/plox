<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Token;

class PloxReturn extends Statement
{
    public function __construct(
        public Token $keyword,
        public ?Expression $value,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPloxReturnStatement($this);
    }
}
