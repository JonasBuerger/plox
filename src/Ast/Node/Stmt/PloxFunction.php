<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;
use Plox\Token;

class PloxFunction extends Stmt
{
    public function __construct(
        public Token $name,
        public array $params,
        public array $body,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitPloxFunctionStmt($this);
    }
}
