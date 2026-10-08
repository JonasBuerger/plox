<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

enum FunctionType
{
    case NONE;
    case FUNCTION;
    case METHOD;
}
