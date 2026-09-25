<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class Variable extends Expr
{
    public function __construct(
        public Token $name,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitVariableExpr($this);
    }
}
