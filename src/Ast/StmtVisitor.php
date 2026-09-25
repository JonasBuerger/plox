<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Stmt\Block;
use Plox\Ast\Node\Stmt\Expression;
use Plox\Ast\Node\Stmt\PloxBreak;
use Plox\Ast\Node\Stmt\PloxClass;
use Plox\Ast\Node\Stmt\PloxFunction;
use Plox\Ast\Node\Stmt\PloxIf;
use Plox\Ast\Node\Stmt\PloxPrint;
use Plox\Ast\Node\Stmt\PloxReturn;
use Plox\Ast\Node\Stmt\PloxVar;
use Plox\Ast\Node\Stmt\PloxWhile;

/**
 * @template T
 */
interface StmtVisitor
{
    /**
     * @return T
     */
    public function visitExpressionStmt(Expression $expression);

    /**
     * @return T
     */
    public function visitPloxPrintStmt(PloxPrint $ploxPrint);

    /**
     * @return T
     */
    public function visitPloxVarStmt(PloxVar $ploxVar);

    /**
     * @return T
     */
    public function visitBlockStmt(Block $block);

    /**
     * @return T
     */
    public function visitPloxIfStmt(PloxIf $ploxIf);

    /**
     * @return T
     */
    public function visitPloxWhileStmt(PloxWhile $ploxWhile);

    /**
     * @return T
     */
    public function visitPloxBreakStmt(PloxBreak $ploxBreak);

    /**
     * @return T
     */
    public function visitPloxFunctionStmt(PloxFunction $ploxFunction);

    /**
     * @return T
     */
    public function visitPloxReturnStmt(PloxReturn $ploxReturn);

    /**
     * @return T
     */
    public function visitPloxClassStmt(PloxClass $ploxClass);
}
