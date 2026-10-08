<?php

declare(strict_types=1);

namespace Plox\Ast\Visitor;

enum LoopType
{
    case NONE;
    case WHILE;
}
