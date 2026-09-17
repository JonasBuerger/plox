<?php

declare(strict_types=1);

namespace Plox;

use Plox\Ast\Expression;
use Plox\Ast\Node\Assign;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Block;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Logical;
use Plox\Ast\Node\PloxIf;
use Plox\Ast\Node\PloxPrint;
use Plox\Ast\Node\PloxVar;
use Plox\Ast\Node\PloxWhile;
use Plox\Ast\Node\Unary;
use Plox\Ast\Node\Variable;
use Plox\Ast\Statement;

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
     * @return list<Statement>
     */
    public function parse(): array
    {
        $statements = [];
        while (!$this->isAtEnd()) {
            $statement = $this->declaration();
            if ($statement instanceof Statement) {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    private function expression(): Expression
    {
        return $this->assignment();
    }

    private function assignment(): Expression
    {
        $expression = $this->logicOr();
        if ($this->match(TokenType::EQUAL)) {
            $equals = $this->previous();
            $value = $this->assignment();
            if ($expression instanceof Variable) {
                return new Assign($expression->name, $value);
            }

            $this->error($equals, 'Invalid assignment target.');
        }

        return $expression;
    }

    private function logicOr(): Expression
    {
        $expr = $this->logicAnd();
        while ($this->match(TokenType::OR)) {
            $operator = $this->previous();
            $expr = new Logical($expr, $operator, $this->logicAnd());
        }

        return $expr;
    }

    private function logicAnd(): Expression
    {
        $expr = $this->equality();
        while ($this->match(TokenType::AND)) {
            $operator = $this->previous();
            $expr = new Logical($expr, $operator, $this->equality());
        }

        return $expr;
    }

    private function equality(): Expression
    {
        $expr = $this->comparison();
        while ($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)) {
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function comparison(): Expression
    {
        $expr = $this->term();
        while ($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)) {
            $operator = $this->previous();
            $right = $this->term();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function term(): Expression
    {
        $expr = $this->factor();
        while ($this->match(TokenType::MINUS, TokenType::PLUS)) {
            $operator = $this->previous();
            $right = $this->factor();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function factor(): Expression
    {
        $expr = $this->unary();
        while ($this->match(TokenType::STAR, TokenType::SLASH)) {
            $operator = $this->previous();
            $right = $this->unary();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function unary(): Expression
    {
        if ($this->match(TokenType::BANG, TokenType::MINUS)) {
            return new Unary($this->previous(), $this->unary());
        }

        return $this->primary();
    }

    private function primary(): Expression
    {
        if ($this->match(TokenType::FALSE)) {
            return new Literal(false);
        }
        if ($this->match(TokenType::TRUE)) {
            return new Literal(true);
        }
        if ($this->match(TokenType::NIL)) {
            return new Literal(null);
        }
        if ($this->match(TokenType::NUMBER, TokenType::STRING)) {
            return new Literal($this->previous()->literal);
        }
        if ($this->match(TokenType::IDENTIFIER)) {
            return new Variable($this->previous());
        }
        if ($this->match(TokenType::LEFT_PAREN)) {
            $expr = $this->expression();
            $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after expression.");

            return new Grouping($expr);
        }
        throw $this->error($this->peek(), 'Expression expected.');
    }

    private function consume(TokenType $type, string $message): Token
    {
        if ($this->check($type)) {
            ++$this->current;

            return $this->previous();
        }
        throw $this->error($this->peek(), $message);
    }

    private function error(Token $token, string $message): ParserException
    {
        Plox::error($token, $message);

        return new ParserException($this->peek() . ': ' . $message);
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

    private function previous(): Token
    {
        return $this->tokens[$this->current - 1];
    }

    private function check(TokenType $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        return $this->peek()->type === $type;
    }

    private function isAtEnd(): bool
    {
        return $this->peek()->type === TokenType::EOF;
    }

    private function peek(): Token
    {
        return $this->tokens[$this->current];
    }

    private function statement(): Statement
    {
        return match (true) {
            $this->match(TokenType::PRINT) => $this->printStatement(),
            $this->match(TokenType::IF) => $this->ifStatement(),
            $this->match(TokenType::WHILE) => $this->whileStatement(),
            $this->match(TokenType::FOR) => $this->forStatement(),
            $this->match(TokenType::LEFT_BRACE) => new Block($this->block()),
            default => $this->expressionStatement(),
        };
    }

    /**
     * @return list<Statement>
     */
    private function block(): array
    {
        $statements = [];
        while (!$this->isAtEnd() && !$this->check(TokenType::RIGHT_BRACE)) {
            $statement = $this->declaration();
            if ($statement instanceof Statement) {
                $statements[] = $statement;
            }
        }

        $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");

        return $statements;
    }

    private function printStatement(): PloxPrint
    {
        $value = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");

        return new PloxPrint($value);
    }

    private function expressionStatement(): Ast\Node\Expression
    {
        $value = $this->expression();
        $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");

        return new Ast\Node\Expression($value);
    }

    private function declaration(): ?Statement
    {
        try {
            if ($this->match(TokenType::VAR)) {
                return $this->varDeclaration();
            }

            return $this->statement();
        } catch (ParserException) {
            $this->synchronize();

            return null;
        }
    }

    private function varDeclaration(): PloxVar
    {
        $name = $this->consume(TokenType::IDENTIFIER, 'Expect variable name.');
        $initializer = null;
        if ($this->match(TokenType::EQUAL)) {
            $initializer = $this->expression();
        }
        $this->consume(TokenType::SEMICOLON, "Expected ';' after variable declaration.");

        return new PloxVar($name, $initializer);
    }

    private function ifStatement(): PloxIf
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");
        $thenBranch = $this->statement();
        $elseBranch = null;
        if ($this->match(TokenType::ELSE)) {
            $elseBranch = $this->statement();
        }

        return new PloxIf($condition, $thenBranch, $elseBranch);
    }

    private function whileStatement(): PloxWhile
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");
        $body = $this->statement();

        return new PloxWhile($condition, $body);
    }

    private function forStatement(): Block
    {
        $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
        $initializer = null;
        if ($this->match(TokenType::VAR)) {
            $initializer = $this->varDeclaration();
        } elseif (!$this->match(TokenType::SEMICOLON)) {
            $initializer = $this->expressionStatement();
        }
        $condition = null;
        if (!$this->match(TokenType::SEMICOLON)) {
            $condition = $this->expression();
            $this->consume(TokenType::SEMICOLON, "Expected ';' after for condition.");
        }
        $increment = null;
        if (!$this->match(TokenType::RIGHT_PAREN)) {
            $increment = $this->expression();
        }
        $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");
        $body = $this->statement();

        $whileStatement = new PloxWhile($condition, new Block(array_filter([$body, $increment])));

        return new Block(array_filter([$initializer, $whileStatement]));
    }
}
