<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Expression;
use Plox\Ast\Node\Printing;
use Plox\Ast\Node\VarSt;

/**
 * @template T
 */
interface StatementVisitor
{
    /**
     * @return T
     */
    public function visitExpressionStatement(Expression $expression);

    /**
     * @return T
     */
    public function visitPrintingStatement(Printing $printing);

    /**
     * @return T
     */
    public function visitVarStStatement(VarSt $varst);
}
