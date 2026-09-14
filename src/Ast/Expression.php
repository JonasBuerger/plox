<?php

declare(strict_types=1);

namespace Plox\Ast;

abstract class Expression
{
    /**
     * @template T
     *
     * @param ExpressionVisitor<T> $visitor
     *
     * @return T
     */
    abstract public function accept(ExpressionVisitor $visitor): mixed;
}
