<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Expr;
use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;

class PloxPrint extends Stmt
{
    public function __construct(
        public Expr $expression,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitPloxPrintStmt($this);
    }
}
