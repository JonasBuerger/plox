<?php

declare(strict_types=1);

namespace App\Tests\Ast\Visitor;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Plox\Ast\ExprVisitor;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;
use Plox\Ast\Visitor\AstPrinter;
use Plox\Ast\Visitor\RPNPrinter;
use Plox\Token;
use Plox\TokenType;

/**
 * @internal
 */
#[CoversClass(AstPrinter::class)]
#[CoversClass(RPNPrinter::class)]
#[Small]
final class VisitorPrinterTest extends TestCase
{
    /**
     * @return Generator<array{printerClass:class-string<ExprVisitor<string>>,example1:string,example2:string}>
     */
    public static function providePrintVisitors(): Generator
    {
        yield 'Ast' => ['printerClass' => AstPrinter::class, 'example1' => '(* (- 123) (group 45.67))', 'example2' => '(* (group (+ 1 2)) (group (- 4 3)))'];
        yield 'RPN' => ['printerClass' => RPNPrinter::class, 'example1' => '123 #- 45.67 *', 'example2' => '1 2 + 4 3 - *'];
    }

    /**
     * @param class-string<ExprVisitor<string>> $printerClass
     */
    #[DataProvider('providePrintVisitors')]
    public function testPrintAst(string $printerClass, string $example1, string $example2): void
    {
        $printer = new $printerClass();
        $expr = new Binary(
            new Unary(
                new Token(TokenType::MINUS, '-', null, 1),
                new Literal(123),
            ),
            new Token(TokenType::STAR, '*', null, 1),
            new Grouping(new Literal(45.67)),
        );

        self::assertSame($example1, $expr->accept($printer));
        $expr = new Binary(
            new Grouping(
                new Binary(
                    new Literal(1),
                    new Token(TokenType::PLUS, '+', null, 1),
                    new Literal(2),
                ),
            ),
            new Token(TokenType::STAR, '*', null, 1),
            new Grouping(new Binary(
                new Literal(4),
                new Token(TokenType::MINUS, '-', null, 1),
                new Literal(3),
            )),
        );
        self::assertSame($example2, $expr->accept($printer));
    }
}
