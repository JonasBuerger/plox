<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Expr\Assign;
use Plox\Ast\Node\Expr\Binary;
use Plox\Ast\Node\Expr\Call;
use Plox\Ast\Node\Expr\Grouping;
use Plox\Ast\Node\Expr\Literal;
use Plox\Ast\Node\Expr\Logical;
use Plox\Ast\Node\Expr\Unary;
use Plox\Ast\Node\Expr\Variable;

/**
 * @template T
 */
interface ExprVisitor
{
    /**
     * @return T
     */
    public function visitBinaryExpr(Binary $binary);

    /**
     * @return T
     */
    public function visitGroupingExpr(Grouping $grouping);

    /**
     * @return T
     */
    public function visitLiteralExpr(Literal $literal);

    /**
     * @return T
     */
    public function visitUnaryExpr(Unary $unary);

    /**
     * @return T
     */
    public function visitVariableExpr(Variable $variable);

    /**
     * @return T
     */
    public function visitAssignExpr(Assign $assign);

    /**
     * @return T
     */
    public function visitLogicalExpr(Logical $logical);

    /**
     * @return T
     */
    public function visitCallExpr(Call $call);
}
