<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Expression;
use Plox\Ast\Node\Printing;
use Plox\Ast\Node\VarSt;
use Plox\Ast\Node\Block;

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

    /**
     * @return T
     */
    public function visitBlockStatement(Block $block);
}
