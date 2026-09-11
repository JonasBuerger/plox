<?php
declare(strict_types=1);

namespace Plox;

final class Interpreter
{
    public function __construct(private string $projectDir)
    {
    }

    public function runPrompt(): void
    {
        $stdin = fopen('php://stdin', 'r');
        for (; ;) {
            echo '> ';
            $line = trim(fgets($stdin));
            if ($line === '') break;
            $this->run($line);
        }
    }

    public function runFile(string $path): void
    {
        $this->run(file_get_contents($path));
    }

    private function run(string $code): void
    {
        $scanner = new Scanner($code);
        foreach ($scanner->scanTokens() as $token) {
            echo $token;
        }
        echo PHP_EOL;
    }
}
