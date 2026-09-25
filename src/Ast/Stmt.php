<?php

declare(strict_types=1);

namespace Plox\Ast;

abstract class Stmt
{
    /**
     * @template T
     *
     * @param StmtVisitor<T> $visitor
     *
     * @return T
     */
    abstract public function accept(StmtVisitor $visitor): mixed;
}
