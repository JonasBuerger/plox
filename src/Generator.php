<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Expression;

class Generator
{
    /**
     * @var array<string, array<string, string>>
     */
    private static array $astNodes = [
        'Binary' => ['left' => Expression::class, 'operator' => Token::class, 'right' => Expression::class],
        'Grouping' => ['expression' => Expression::class],
        'Literal' => ['value' => 'string|float|bool|null'],
        'Unary' => ['operator' => Token::class, 'right' => Expression::class],
    ];

    public static function generateAstClasses(string $projectDir): int
    {
        $exitCode = ExitCode::SUCCESS->value;
        // Generate Ast Node Classes
        foreach (self::$astNodes as $class => $node) {
            $content = <<<CONTENT
                <?php
                declare(strict_types=1);

                namespace Plox\\Ast\\Node;

                class $class extends \\Plox\\Ast\\Expression
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
                    public function accept(\\Plox\\Ast\\ExpressionVisitor \$visitor): mixed
                    {
                        return \$visitor->visit$class(\$this);
                    }
                }
                CONTENT;
            file_put_contents($projectDir . '/src/Ast/Node/' . $class . '.php', $content);
        }

        // Generate ExpressionVisitor Interface
        $content = <<<CONTENT
            <?php

            declare(strict_types=1);

            namespace Plox\Ast;

            /**
             * @template T
             */
            interface ExpressionVisitor
            {
            CONTENT;
        foreach (self::$astNodes as $class => $node) {
            $content .= '/**' . PHP_EOL . '* @return T' . PHP_EOL . '*/' . PHP_EOL;
            $param = strtolower($class);
            $content .= "public function visit$class(\\Plox\\Ast\\Node\\$class \$$param): mixed;" . PHP_EOL;
        }
        $content .= '}';
        file_put_contents($projectDir . '/src/Ast/ExpressionVisitor.php', $content);

        // Generate Expression Base Class
        $content = <<<'CONTENT'
            <?php

            declare(strict_types=1);

            namespace Plox\Ast;

            abstract class Expression
            {
                /**
                 * @template T
                 * @param ExpressionVisitor<T> $visitor
                 * @return T
                 */
                abstract public function accept(ExpressionVisitor $visitor): mixed;
            }
            CONTENT;
        file_put_contents($projectDir . '/src/Ast/Expression.php', $content);

        // Fix Code-Style
        if (is_executable($projectDir . '/bin/format')) {
            exec($projectDir . '/bin/format --quiet', result_code: $exitCode);
        }

        return $exitCode;
    }
}
