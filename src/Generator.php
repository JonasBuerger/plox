<?php

declare(strict_types=1);

namespace Plox;

class Generator
{
    /**
     * @var array<string, array<string, string>>
     */
    private static array $astNodes = [
        'Binary' => ['left' => Expression::class, 'operator' => Token::class, 'right' => Expression::class],
        'Grouping' => ['expression' => Expression::class],
        'Literal' => ['value' => 'object'],
        'Unary' => ['operator' => Token::class, 'right' => Expression::class],
    ];

    public static function generateAstClasses(string $projectDir): int
    {
        $exitCode = ExitCode::SUCCESS->value;
        foreach (self::$astNodes as $class => $node) {
            $content = <<<CONTENT
                <?php
                declare(strict_types=1);

                namespace Plox\Ast;

                class $class extends \Plox\Expression
                {
                    public function __construct(
                CONTENT;
            foreach ($node as $param => $type) {
                $content .= 'public \\' . $type . ' $' . $param . ',' . PHP_EOL;
            }
            $content .= <<<CONTENT
                    ) {
                    }
                }
                CONTENT;
            file_put_contents($projectDir . '/src/Ast/' . $class . '.php', $content);
        }
        if (is_executable($projectDir . '/vendor/bin/php-cs-fixer')) {
            passthru($projectDir . '/vendor/bin/php-cs-fixer fix', $exitCode);

            if ($exitCode !== ExitCode::SUCCESS->value) {
                return $exitCode;
            }
        }
        if (is_executable($projectDir . '/vendor/bin/rector')) {
            passthru($projectDir . '/vendor/bin/rector', $exitCode);
        }

        return $exitCode;
    }
}
