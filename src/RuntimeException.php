<?php

declare(strict_types=1);

namespace Plox;

class RuntimeException extends \RuntimeException
{
    public function __construct(private readonly Token $token, string $message = '')
    {
        parent::__construct($message);
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}
