<?php

declare(strict_types=1);

namespace Plox\Ast\Node;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;

class Literal extends Expression
{
    public function __construct(
        public string|float|bool|null $value,
    ) {
    }

    public function accept(ExpressionVisitor $visitor): mixed
    {
        return $visitor->visitLiteralExpression($this);
    }
}
