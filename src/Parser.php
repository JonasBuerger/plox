<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Expr;
use Plox\Ast\Node\Expr as Expression;
use Plox\Ast\Node\Stmt as Statement;
use Plox\Ast\Stmt;

final class Parser
{
    private int $current = 0;

    public function __construct(
        /**
         * @var list<Token>
         */
        private array $tokens,
    ) {
    }

    /**
     * @return list<Stmt>
     */
    public function parse(): array
    {
        $statements = [];
        while (!$this->isAtEnd()) {
            $statement = $this->declaration();
            if ($statement instanceof Stmt) {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    private function assignment(): Expr
    {
        $expression = $this->logicOr();
        if ($this->match(TokenType::EQUAL)) {
            $equals = $this->previous();
            $value = $this->assignment();
            if ($expression instanceof Expression\Variable) {
                return new Expression\Assign($expression->name, $value);
            }
            if ($expression instanceof Expression\Get) {
                return new Expression\Set($expression->object, $expression->name, $value);
            }

            $this->error($equals, 'Invalid assignment target.');
        }

        return $expression;
    }

    /**
     * @return list<Stmt>
     */
    private function block(): array
    {
        $statements = [];
        while (!$this->isAtEnd() && !$this->check(TokenType::RIGHT_BRACE)) {
            $statement = $this->declaration();
            if ($statement instanceof Stmt) {
                $statements[] = $statement;
            }
        }

        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");

        return $statements;
    }

    private function breakStatement(): Statement\PloxBreak
    {
        $node = new Statement\PloxBreak($this->previous());
        $this->consume(TokenType::SEMICOLON, "Expected ';' after break.");

        return $node;
    }

    private function call(): Expr
    {
        $expression = $this->primary();

        while (true) {
            if ($this->match(TokenType::LEFT_PAREN)) {
                $expression = $this->finishCall($expression);
            } elseif ($this->match(TokenType::DOT)) {
                $name = $this->consume(TokenType::IDENTIFIER, "Expect property name after '.'.");
                $expression = new Expression\Get($expression, $name);
            } else {
                break;
            }
        }

        return $expression;
    }

    private function check(TokenType $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        return $this->peek()->type === $type;
    }

    private function classDeclaration(): Statement\PloxClass
    {
        $name = $this->consume(TokenType::IDENTIFIER, 'Expect class name.');

        $superClass = null;
        if ($this->match(TokenType::LESS)) {
            $superClass = new Expression\Variable($this->consume(TokenType::IDENTIFIER, "Expect superclass name after '<'."));
        }

        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before class body.");

        $methods = [];
        while (!$this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
            $methods[] = $this->function('method');
        }

        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after class body.");

        return new Statement\PloxClass($name, $superClass, $methods);
    }

    private function comparison(): Expr
    {
        $expr = $this->term();
        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator = $this->previous();
            $right = $this->term();
            $expr = new Expression\Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function consume(TokenType $type, string $message): Token
    {
        if ($this->check($type)) {
            ++$this->current;

            return $this->previous();
        }
        throw $this->error($this->peek(), $message);
    }

    private function declaration(): ?Stmt
    {
        try {
            if ($this->match(TokenType::TYPE_CLASS)) {
                return $this->classDeclaration();
            }
            if ($this->match(TokenType::FUN)) {
                return $this->function('function');
            }
            if ($this->match(TokenType::VAR)) {
                return $this->varDeclaration();
            }

            return $this->statement();
        } catch (ParserException) {
            $this->synchronize();

            return null;
        }
    }

    private function equality(): Expr
    {
        $expr = $this->comparison();
        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new Expression\Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function error(Token $token, string $message): ParserException
    {
        Plox::error($token, $message);

        return new ParserException($this->peek() . ': ' . $message);
    }

    private function expression(): Expr
    {
        return $this->assignment();
    }

    private function expressionStatement(): Statement\Expression
    {
        $value = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");

        return new Statement\Expression($value);
    }

    private function factor(): Expr
    {
        $expr = $this->unary();
        while ($this->match(TokenType::STAR, TokenType::SLASH)) {
            $operator = $this->previous();
            $right = $this->unary();
            $expr = new Expression\Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function finishCall(Expr $callee): Expression\Call
    {
        $arguments = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($arguments) >= 255) {
                    $this->error($this->peek(), "Can't have more than 255 arguments.");
                }
                $arguments[] = $this->expression();
            } while ($this->match(TokenType::COMMA));
        }
        $paren = $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after arguments.");

        return new Expression\Call($callee, $paren, $arguments);
    }

    private function forStatement(): Stmt
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        if ($this->match(TokenType::SEMICOLON)) {
            $initializer = null;
        } elseif ($this->match(TokenType::VAR)) {
            $initializer = $this->varDeclaration();
        } else {
            $initializer = $this->expressionStatement();
        }
        $condition = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $condition = $this->expression();
        }
        $this->consume(TokenType::SEMICOLON, "Expected ';' after for condition.");
        $increment = null;
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $increment = $this->expression();
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");

        $body = $this->statement();
        if ($increment instanceof Expr) {
            $body = new Statement\Block([$body, new Statement\Expression($increment)]);
        }
        $condition ??= new Expression\Literal(true);

        $body = new Statement\PloxWhile($condition, $body);
        if ($initializer !== null) {
            $body = new Statement\Block([$initializer, $body]);
        }

        return $body;
    }

    private function function(string $kind): Statement\PloxFunction
    {
        $name = $this->consume(TokenType::IDENTIFIER, "Expect $kind name.");
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after $kind name.");
        $parameters = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            do {
                if (count($parameters) > 255) {
                    $this->error($this->peek(), "Can't have more than 255 parameters.");
                }
                $parameters[] = $this->consume(TokenType::IDENTIFIER, 'Expect parameter name.');
            } while ($this->match(TokenType::COMMA));
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after parameters.");
        $this->consume(TokenType::LEFT_BRACE, "Expect '{' before $kind body.");
        $body = $this->block();

        return new Statement\PloxFunction($name, $parameters, $body);
    }

    private function ifStatement(): Statement\PloxIf
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");
        $thenBranch = $this->statement();
        $elseBranch = null;
        if ($this->match(TokenType::ELSE)) {
            $elseBranch = $this->statement();
        }

        return new Statement\PloxIf($condition, $thenBranch, $elseBranch);
    }

    private function isAtEnd(): bool
    {
        return $this->peek()->type === TokenType::EOF;
    }

    private function logicAnd(): Expr
    {
        $expr = $this->equality();
        while ($this->match(TokenType::AND)) {
            $operator = $this->previous();
            $expr = new Expression\Logical($expr, $operator, $this->equality());
        }

        return $expr;
    }

    private function logicOr(): Expr
    {
        $expr = $this->logicAnd();
        while ($this->match(TokenType::OR)) {
            $operator = $this->previous();
            $expr = new Expression\Logical($expr, $operator, $this->logicAnd());
        }

        return $expr;
    }

    private function match(TokenType ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                ++$this->current;

                return true;
            }
        }

        return false;
    }

    private function peek(): Token
    {
        return $this->tokens[$this->current];
    }

    private function previous(): Token
    {
        return $this->tokens[$this->current - 1];
    }

    private function primary(): Expr
    {
        if ($this->match(TokenType::FALSE)) {
            return new Expression\Literal(false);
        }
        if ($this->match(TokenType::TRUE)) {
            return new Expression\Literal(true);
        }
        if ($this->match(TokenType::NIL)) {
            return new Expression\Literal(null);
        }
        if ($this->match(TokenType::NUMBER, TokenType::STRING)) {
            return new Expression\Literal($this->previous()->literal);
        }
        if ($this->match(TokenType::SUPER)) {
            $keyword = $this->previous();
            $this->consume(TokenType::DOT, "Expect '.' after 'super'.");
            $method = $this->consume(TokenType::IDENTIFIER, 'Expect superclass method name.');

            return new Expression\Super($keyword, $method);
        }
        if ($this->match(TokenType::THIS)) {
            return new Expression\PloxThis($this->previous());
        }
        if ($this->match(TokenType::IDENTIFIER)) {
            return new Expression\Variable($this->previous());
        }
        if ($this->match(TokenType::LEFT_PAREN)) {
            $expr = $this->expression();
            $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after expression.");

            return new Expression\Grouping($expr);
        }
        throw $this->error($this->peek(), 'Expression expected.');
    }

    private function printStatement(): Statement\PloxPrint
    {
        $value = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");

        return new Statement\PloxPrint($value);
    }

    private function returnStatement(): Statement\PloxReturn
    {
        $keyword = $this->previous();
        $value = null;
        if (!$this->check(TokenType::SEMICOLON)) {
            $value = $this->expression();
        }
        $this->consume(TokenType::SEMICOLON, "Expected ';' after return.");

        return new Statement\PloxReturn($keyword, $value);
    }

    private function statement(): Stmt
    {
        return match (true) {
            $this->match(TokenType::PRINT) => $this->printStatement(),
            $this->match(TokenType::IF) => $this->ifStatement(),
            $this->match(TokenType::WHILE) => $this->whileStatement(),
            $this->match(TokenType::FOR) => $this->forStatement(),
            $this->match(TokenType::LEFT_BRACE) => new Statement\Block($this->block()),
            $this->match(TokenType::BREAK) => $this->breakStatement(),
            $this->match(TokenType::RETURN) => $this->returnStatement(),
            default => $this->expressionStatement(),
        };
    }

    private function synchronize(): void
    {
        ++$this->current;
        while (!$this->isAtEnd()) {
            if ($this->previous()->type === TokenType::SEMICOLON) {
                return;
            }
            if (in_array($this->peek()->type, [
                TokenType::TYPE_CLASS,
                TokenType::FUN,
                TokenType::VAR,
                TokenType::FOR,
                TokenType::IF,
                TokenType::WHILE,
                TokenType::PRINT,
                TokenType::RETURN,
            ])) {
                return;
            }
            ++$this->current;
        }
    }

    private function term(): Expr
    {
        $expr = $this->factor();
        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator = $this->previous();
            $right = $this->factor();
            $expr = new Expression\Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function unary(): Expr
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            return new Expression\Unary($this->previous(), $this->unary());
        }

        return $this->call();
    }

    private function varDeclaration(): Statement\PloxVar
    {
        $name = $this->consume(TokenType::IDENTIFIER, 'Expect variable name.');
        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression();
        }
        $this->consume(TokenType::SEMICOLON, "Expected ';' after variable declaration.");

        return new Statement\PloxVar($name, $initializer);
    }

    private function whileStatement(): Statement\PloxWhile
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");
        $body = $this->statement();

        return new Statement\PloxWhile($condition, $body);
    }
}
