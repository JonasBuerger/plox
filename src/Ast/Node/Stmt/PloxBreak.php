<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Stmt;

use Plox\Ast\Stmt;
use Plox\Ast\StmtVisitor;
use Plox\Token;

class PloxBreak extends Stmt
{
    public function __construct(
        public Token $keyword,
    ) {
    }

    public function accept(StmtVisitor $visitor): mixed
    {
        return $visitor->visitPloxBreakStmt($this);
    }
}
