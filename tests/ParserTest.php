<?php

declare(strict_types=1);

namespace App\Tests;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Plox\Ast\Visitor\RPNPrinter;
use Plox\Parser;
use Plox\Scanner;

/**
 * @internal
 */
#[CoversClass(Parser::class)]
#[Small]
final class ParserTest extends TestCase
{
    /**
     * @return Generator<array{expression:string,expected:string}>
     */
    public static function provideExpressions(): Generator
    {
        yield 'Addition' => ['expression' => '1+2', 'expected' => '1 2 +'];
        yield 'Parenthesis' => ['expression' => '2*(3-1)', 'expected' => '2 3 1 - *'];
        yield 'Simple Precedence' => ['expression' => '2 * 3 - 1 / 5', 'expected' => '2 3 * 1 5 / -'];
        yield 'Comparisons' => ['expression' => '"apple" == "orange" <= "pear" != "hat"', 'expected' => '"apple" "orange" "pear" <= == "hat" !='];
    }

    #[DataProvider('provideExpressions')]
    public function testParsing(string $expression, string $expected): void
    {
        $scanner = new Scanner($expression);
        $parser = new Parser($scanner->scanTokens());
        $rpnNotation = $parser->parse()->accept(new RPNPrinter());
        self::assertSame($expected, $rpnNotation);
    }
}
