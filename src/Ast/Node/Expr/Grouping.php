<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;

class Grouping extends Expr
{
    public function __construct(
        public Expr $expression,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitGroupingExpr($this);
    }
}
