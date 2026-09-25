<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Expr;
use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;

class PloxWhile extends Stmt
{
    public function __construct(
        public Expr $condition,
        public Stmt $body,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitPloxWhileStmt($this);
    }
}
