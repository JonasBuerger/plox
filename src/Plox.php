<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Visitor\Interpreter;

final class Plox
{
    public static bool $hadError = false;
    private static Interpreter $interpreter;

    public static function runPrompt(): int
    {
        $stdin = fopen('php://stdin', 'r');
        while (true) {
            echo '> ';
            $line = trim(fgets($stdin));
            if ($line === '') {
                break;
            }
            self::run($line);
            self::$hadError = false;
        }

        return ExitCode::SUCCESS->value;
    }

    public static function runFile(string $path): int
    {
        self::run(file_get_contents($path));

        return self::$hadError ? ExitCode::EX_SOFTWARE->value : ExitCode::SUCCESS->value;
    }

    private static function run(string $code): void
    {
        $scanner = new Scanner($code);
        $tokens = $scanner->scanTokens();
        if (!self::$hadError) {
            $parser = new Parser($tokens);
            $ast = $parser->parse();
            if (!self::$hadError) {
                self::$interpreter ??= new Interpreter();
                self::$interpreter->interpret($ast);
            }
        }
    }

    public static function error(Token $token, string $message): void
    {
        if ($token->type == TokenType::EOF) {
            self::report($token->line, ' at end', $message);
        } else {
            self::report($token->line, " at '" . $token->lexeme . "'", $message);
        }
    }

    private static function report(int $line, string $where, string $message): void
    {
        echo '[line', $line, '] Error', $where, ': ', $message, PHP_EOL;
        self::$hadError = true;
    }
}
