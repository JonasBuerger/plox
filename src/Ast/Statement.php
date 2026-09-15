<?php

declare(strict_types=1);

namespace Plox\Ast;

abstract class Statement
{
    /**
     * @template T
     *
     * @param StatementVisitor<T> $visitor
     *
     * @return T
     */
    abstract public function accept(StatementVisitor $visitor): mixed;
}
