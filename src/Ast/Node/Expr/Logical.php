<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class Logical extends Expr
{
    public function __construct(
        public Expr $left,
        public Token $operator,
        public Expr $right,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitLogicalExpr($this);
    }
}
