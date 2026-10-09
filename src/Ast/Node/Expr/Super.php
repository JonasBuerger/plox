<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class Super extends Expr
{
    public function __construct(
        public Token $keyword,
        public Token $method,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitSuperExpr($this);
    }
}
