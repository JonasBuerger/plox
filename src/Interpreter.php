<?php
declare(strict_types=1);

namespace Plox;

final class Interpreter
{
    public static bool $hadError = false;

    public static function runPrompt(): int
    {
        $stdin = fopen('php://stdin', 'r');
        for (; ;) {
            echo '> ';
            $line = trim(fgets($stdin));
            if ($line === '') break;
            static::run($line);
            static::$hadError = false;
        }
        return ExitCode::SUCCESS->value;
    }

    public static function runFile(string $path): int
    {
        static::run(file_get_contents($path));
        return static::$hadError ? ExitCode::EX_SOFTWARE->value : ExitCode::SUCCESS->value;
    }

    private static function run(string $code): void
    {
        $scanner = new Scanner($code);
        foreach ($scanner->scanTokens() as $token) {
            echo $token;
        }
        echo PHP_EOL;
    }

    public static function error(int $line, string $message): void
    {
        static::report($line, "", $message);
    }

    private static function report(int $line, string $where, string $message): void
    {
        echo '[line', $line, '] Error', $where, ': ', $message, PHP_EOL;
        static::$hadError = true;
    }
}
