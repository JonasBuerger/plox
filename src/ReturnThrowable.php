<?php

declare(strict_types=1);

namespace Plox;

use RuntimeException;

class ReturnThrowable extends RuntimeException
{
    public function __construct(private readonly Token $token, private readonly mixed $value)
    {
        parent::__construct();
    }

    public function getReturnToken(): mixed
    {
        return $this->token;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
