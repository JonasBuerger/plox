<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;

class Block extends Stmt
{
    public function __construct(
        public array $statements,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitBlockStmt($this);
    }
}
