<?php

declare(strict_types=1);

namespace Plox\Ast;

abstract class Expr
{
    /**
     * @template T
     *
     * @param ExprVisitor<T> $visitor
     *
     * @return T
     */
    abstract public function accept(ExprVisitor $visitor): mixed;
}
