<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Expression;

class Generator
{
    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private static array $astNodes = [
        'Expression' => [
            'Binary' => ['left' => Expression::class, 'operator' => Token::class, 'right' => Expression::class],
            'Grouping' => ['expression' => Expression::class],
            'Literal' => ['value' => 'string|float|bool|null'],
            'Unary' => ['operator' => Token::class, 'right' => Expression::class],
            'Variable' => ['name' => Token::class],
        ],
        'Statement' => [
            'Expression' => ['expression' => Expression::class],
            // print is a reserved keyword in PHP
            'Printing' => ['expression' => Expression::class],
            // var is a reserved keyword in PHP
            'VarSt' => ['name' => Token::class, 'initializer' => Expression::class],
        ],
    ];

    public static function generateAstClasses(string $projectDir): int
    {
        $exitCode = ExitCode::SUCCESS->value;

        foreach (self::$astNodes as $root => $tree) {
            self::defineAst($projectDir . '/src/Ast', $root, 'Plox\\Ast', $tree);
        }

        // Fix Code-Style
        if (is_executable($projectDir . '/bin/format')) {
            exec($projectDir . '/bin/format --quiet', result_code: $exitCode);
        }

        return $exitCode;
    }

    /**
     * @param array<string, array<string, string>> $nodes
     */
    private static function defineAst(string $outputDir, string $baseClass, string $baseNamespace, array $nodes): void
    {
        // Generate Ast Node Classes
        foreach ($nodes as $class => $node) {
            $content = <<<CONTENT
                <?php
                declare(strict_types=1);

                namespace $baseNamespace\\Node;

                class $class extends \\$baseNamespace\\$baseClass
                {
                    public function __construct(

                CONTENT;
            foreach ($node as $param => $type) {
                $content .= 'public \\' . $type . ' $' . $param . ',' . PHP_EOL;
            }
            $content .= <<<CONTENT
                    ) {
                    }

                    /**
                     * @inheritDoc
                     */
                    public function accept(\\$baseNamespace\\{$baseClass}Visitor \$visitor)
                    {
                        return \$visitor->visit$class$baseClass(\$this);
                    }
                }
                CONTENT;
            file_put_contents($outputDir . '/Node/' . $class . '.php', $content);
        }

        // Generate ExpressionVisitor Interface
        $content = <<<CONTENT
            <?php

            declare(strict_types=1);

            namespace $baseNamespace;

            /**
             * @template T
             */
            interface {$baseClass}Visitor
            {
            CONTENT;
        foreach ($nodes as $class => $node) {
            $content .= '/**' . PHP_EOL . '* @return T' . PHP_EOL . '*/' . PHP_EOL;
            $param = strtolower($class);
            $content .= "public function visit$class$baseClass(\\$baseNamespace\\Node\\$class \$$param);" . PHP_EOL;
        }
        $content .= '}';
        file_put_contents($outputDir . '/' . $baseClass . 'Visitor.php', $content);

        // Generate Expression Base Class
        $content = <<<CONTENT
            <?php

            declare(strict_types=1);

            namespace $baseNamespace;

            abstract class {$baseClass}
            {
                /**
                 * @template T
                 * @param {$baseClass}Visitor<T> \$visitor
                 * @return T
                 */
                abstract public function accept({$baseClass}Visitor \$visitor): mixed;
            }
            CONTENT;
        file_put_contents($outputDir . '/' . $baseClass . '.php', $content);
    }
}
