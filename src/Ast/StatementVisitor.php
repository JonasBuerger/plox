<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Block;
use Plox\Ast\Node\Expression;
use Plox\Ast\Node\PloxBreak;
use Plox\Ast\Node\PloxFunction;
use Plox\Ast\Node\PloxIf;
use Plox\Ast\Node\PloxPrint;
use Plox\Ast\Node\PloxReturn;
use Plox\Ast\Node\PloxVar;
use Plox\Ast\Node\PloxWhile;

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
    public function visitPloxPrintStatement(PloxPrint $ploxPrint);

    /**
     * @return T
     */
    public function visitPloxVarStatement(PloxVar $ploxVar);

    /**
     * @return T
     */
    public function visitBlockStatement(Block $block);

    /**
     * @return T
     */
    public function visitPloxIfStatement(PloxIf $ploxIf);

    /**
     * @return T
     */
    public function visitPloxWhileStatement(PloxWhile $ploxWhile);

    /**
     * @return T
     */
    public function visitPloxBreakStatement(PloxBreak $ploxBreak);

    /**
     * @return T
     */
    public function visitPloxFunctionStatement(PloxFunction $ploxFunction);

    /**
     * @return T
     */
    public function visitPloxReturnStatement(PloxReturn $ploxReturn);
}
