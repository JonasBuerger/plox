<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Statement;
use Plox\Ast\StatementVisitor;
use Plox\Token;

class PloxFunction extends Statement
{
    public function __construct(
        public Token $name,
        public array $params,
        public array $body,
    ) {
    }

    public function accept(StatementVisitor $visitor): mixed
    {
        return $visitor->visitPloxFunctionStatement($this);
    }
}
