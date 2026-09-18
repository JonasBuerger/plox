<?php

declare(strict_types=1);

namespace Plox;

use RuntimeException;

class PloxBreak extends RuntimeException
{
    public function __construct(private readonly Token $token)
    {
        parent::__construct();
    }

    public function getBreakToken(): Token
    {
        return $this->token;
    }
}
