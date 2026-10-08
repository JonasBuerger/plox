<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class PloxThis extends Expr
{
    public function __construct(
        public Token $keyword,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitPloxThisExpr($this);
    }
}
