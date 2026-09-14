<?php

declare(strict_types=1);

namespace Plox\Ast;

use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;

/**
 * @template T
 */
interface ExpressionVisitor
{
    /**
     * @return T
     */
    public function visitBinary(Binary $binary): mixed;

    /**
     * @return T
     */
    public function visitGrouping(Grouping $grouping): mixed;

    /**
     * @return T
     */
    public function visitLiteral(Literal $literal): mixed;

    /**
     * @return T
     */
    public function visitUnary(Unary $unary): mixed;
}
