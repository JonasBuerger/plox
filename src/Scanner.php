<?php
declare(strict_types=1);

namespace Plox;

readonly class Scanner
{
    public function __construct(private string $source)
    {
    }

    public function scanTokens(): array
    {
        return str_split($this->source);
    }
}
