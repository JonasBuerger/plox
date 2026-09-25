<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Expr;
use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;
use Plox\Token;

class PloxVar extends Stmt
{
    public function __construct(
        public Token $name,
        public ?Expr $initializer,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitPloxVarStmt($this);
    }
}
