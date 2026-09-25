<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;

class Literal extends Expr
{
    public function __construct(
        public mixed $value,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitLiteralExpr($this);
    }
}
