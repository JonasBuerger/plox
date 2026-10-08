<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class Get extends Expr
{
    public function __construct(
        public Expr $object,
        public Token $name,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitGetExpr($this);
    }
}
