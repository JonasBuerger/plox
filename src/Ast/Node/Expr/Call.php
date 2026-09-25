<?php

declare(strict_types=1);

namespace Plox\Ast\Node\Expr;

use Plox\Ast\Expr;
use Plox\Ast\ExprVisitor;
use Plox\Token;

class Call extends Expr
{
    public function __construct(
        public Expr $callee,
        public Token $paren,
        public array $arguments,
    ) {
    }

    public function accept(ExprVisitor $visitor): mixed
    {
        return $visitor->visitCallExpr($this);
    }
}
