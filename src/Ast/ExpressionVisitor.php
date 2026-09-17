<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Assign;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Logical;
use Plox\Ast\Node\Unary;
use Plox\Ast\Node\Variable;

/**
 * @template T
 */
interface ExpressionVisitor
{
    /**
     * @return T
     */
    public function visitBinaryExpression(Binary $binary);

    /**
     * @return T
     */
    public function visitGroupingExpression(Grouping $grouping);

    /**
     * @return T
     */
    public function visitLiteralExpression(Literal $literal);

    /**
     * @return T
     */
    public function visitUnaryExpression(Unary $unary);

    /**
     * @return T
     */
    public function visitVariableExpression(Variable $variable);

    /**
     * @return T
     */
    public function visitAssignExpression(Assign $assign);

    /**
     * @return T
     */
    public function visitLogicalExpression(Logical $logical);
}
