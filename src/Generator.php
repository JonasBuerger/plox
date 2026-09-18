<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Expression;
use Plox\Ast\Statement;

class Generator
{
    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private static array $astNodes = [
        'Expression' => [
            'Binary' => ['left' => Expression::class, 'operator' => Token::class, 'right' => Expression::class],
            'Grouping' => ['expression' => Expression::class],
            'Literal' => ['value' => 'mixed'],
            'Unary' => ['operator' => Token::class, 'right' => Expression::class],
            'Variable' => ['name' => Token::class],
            'Assign' => ['name' => Token::class, 'value' => Expression::class],
            'Logical' => ['left' => Expression::class, 'operator' => Token::class, 'right' => Expression::class],
            'Call' => ['callee' => Expression::class, 'paren' => Token::class, 'arguments' => 'array'],
        ],
        'Statement' => [
            'Expression' => ['expression' => Expression::class],
            // print is a reserved keyword in PHP
            'PloxPrint' => ['expression' => Expression::class],
            // var is a reserved keyword in PHP
            'PloxVar' => ['name' => Token::class, 'initializer' => Expression::class . '|null'],
            'Block' => ['statements' => 'array'],
            // if is a reserved keyword in PHP
            'PloxIf' => ['condition' => Expression::class, 'thenBranch' => Statement::class, 'elseBranch' => Statement::class . '|null'],
            'PloxWhile' => ['condition' => Expression::class, 'body' => Statement::class],
            'PloxBreak' => ['keyword' => Token::class],
            'PloxFunction' => ['name' => Token::class, 'params' => 'array', 'body' => 'array'],
            'PloxReturn' => ['keyword' => Token::class, 'value' => Expression::class . '|null'],
        ],
    ];

    public static function generateAstClasses(string $projectDir): int
    {
        $exitCode = ExitCode::SUCCESS->value;

        foreach (self::$astNodes as $root => $tree) {
            self::defineAst($projectDir . '/src/Ast', $root, $tree);
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
    private static function defineAst(string $outputDir, string $baseClass, array $nodes): void
    {
        // Generate Ast Node Classes
        foreach ($nodes as $class => $node) {
            $content = <<<CONTENT
                <?php
                declare(strict_types=1);

                namespace Plox\\Ast\\Node;

                class $class extends \\Plox\\Ast\\$baseClass
                {
                    public function __construct(

                CONTENT;
            foreach ($node as $param => $type) {
                $type = '\\' . str_replace('|', '|\\', $type);
                $content .= 'public ' . $type . ' $' . $param . ',' . PHP_EOL;
            }
            $content .= <<<CONTENT
                    ) {
                    }

                    /**
                     * @inheritDoc
                     */
                    public function accept(\\Plox\\Ast\\{$baseClass}Visitor \$visitor)
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

            namespace Plox\\Ast;

            /**
             * @template T
             */
            interface {$baseClass}Visitor
            {
            CONTENT;
        foreach ($nodes as $class => $node) {
            $content .= '/**' . PHP_EOL . '* @return T' . PHP_EOL . '*/' . PHP_EOL;
            $param = lcfirst($class);
            $content .= "public function visit$class$baseClass(\\Plox\\Ast\\Node\\$class \$$param);" . PHP_EOL;
        }
        $content .= '}';
        file_put_contents($outputDir . '/' . $baseClass . 'Visitor.php', $content);

        // Generate Tree Base Class
        $content = <<<CONTENT
            <?php

            declare(strict_types=1);

            namespace Plox\Ast;

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
