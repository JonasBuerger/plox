<?php
declare(strict_types=1);

namespace Plox;

class Scanner
{
    /**
     * @var list<Token>
     */
    private array $tokens = [];
    private int $start = 0;
    private int $current = 0;
    private int $line = 1;
    /**
     * @var array<string, TokenType>
     */
    private static array $keywords = [
        'and' => TokenType::AND,
        'class' => TokenType::TYPE_CLASS,
        'else' => TokenType::ELSE,
        'false' => TokenType::FALSE,
        'for' => TokenType::FOR,
        'fun' => TokenType::FUN,
        'if' => TokenType::IF,
        'nil' => TokenType::NIL,
        'or' => TokenType::OR,
        'print' => TokenType::PRINT,
        'return' => TokenType::RETURN,
        'super' => TokenType::SUPER,
        'this' => TokenType::THIS,
        'true' => TokenType::TRUE,
        'var' => TokenType::VAR,
        'while' => TokenType::WHILE,
    ];

    public function __construct(private readonly string $source)
    {
    }

    /**
     * @return list<Token>
     */
    public function scanTokens(): array
    {
        while (!$this->isAtEnd()) {
            $this->start = $this->current;
            $this->scanToken();
        }
        $this->tokens[] = new Token(TokenType::EOF, "", null, $this->line);
        return $this->tokens;
    }

    private function isAtEnd(): bool
    {
        return $this->current >= strlen($this->source);
    }

    private function advance(): string
    {
        return substr($this->source, $this->current++, 1);
    }

    private function addToken(TokenType $type, string|float|null $literal = null): void
    {
        $this->tokens[] = new Token($type, substr($this->source, $this->start, $this->current - $this->start), $literal, $this->line);
    }

    private function scanToken(): void
    {
        $c = $this->advance();
        switch ($c) {
            case '(':
                $this->addToken(TokenType::LEFT_PAREN);
                break;
            case ')':
                $this->addToken(TokenType::RIGHT_PAREN);
                break;
            case '{':
                $this->addToken(TokenType::LEFT_BRACE);
                break;
            case '}':
                $this->addToken(TokenType::RIGHT_BRACE);
                break;
            case ',':
                $this->addToken(TokenType::COMMA);
                break;
            case '.':
                $this->addToken(TokenType::DOT);
                break;
            case '-':
                $this->addToken(TokenType::MINUS);
                break;
            case '+':
                $this->addToken(TokenType::PLUS);
                break;
            case ';':
                $this->addToken(TokenType::SEMICOLON);
                break;
            case '*':
                $this->addToken(TokenType::STAR);
                break;
            case '!':
                $this->addToken($this->match('=') ? TokenType::BANG_EQUAL : TokenType::BANG);
                break;
            case '=':
                $this->addToken($this->match('=') ? TokenType::EQUAL_EQUAL : TokenType::EQUAL);
                break;
            case '<':
                $this->addToken($this->match('=') ? TokenType::LESS_EQUAL : TokenType::LESS);
                break;
            case '>':
                $this->addToken($this->match('=') ? TokenType::GREATER_EQUAL : TokenType::GREATER);
                break;
            case '/':
                if ($this->match('/')) {
                    // A comment goes until the end of the line.
                    while ($this->peek() !== PHP_EOL && !$this->isAtEnd()) {
                        $this->current++;
                    }
                } else {
                    $this->addToken(TokenType::SLASH);
                }
                break;
            case ' ':
            case "\r":
            case "\t":
                // Ignore whitespace.
                break;

            case "\n":
                $this->line++;
                break;
            case '"':
                $this->string();
                break;
            default:
                if (ctype_digit($c)) {
                    $this->number();
                } else if ($this->isAlphaNumeric($c)) {
                    $this->identifier();
                } else {
                    Interpreter::error($this->line, "Unexpected character.");
                }
                break;
        }
    }

    private function match(string $expected): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }
        if (substr($this->source, $this->current, 1) != $expected) {
            return false;
        }
        $this->current++;
        return true;
    }

    private function peek(): string
    {
        if ($this->isAtEnd()) {
            return "\0";
        }
        return substr($this->source, $this->current, 1);
    }

    private function peekNext(): string
    {
        if ($this->current + 1 >= strlen($this->source)) {
            return '\0';
        }
        return substr($this->source, $this->current + 1, 1);
    }

    private function string(): void
    {
        while ($this->peek() !== '"' && !$this->isAtEnd()) {
            if ($this->peek() === "\n") {
                $this->line++;
            }
            $this->current++;
        }
        if ($this->isAtEnd()) {
            Interpreter::error($this->line, 'Unterminated string.');
            return;
        }
        $this->current++;
        $this->addToken(TokenType::STRING, substr($this->source, $this->start + 1, ($this->current - $this->start - 2)));
    }

    private function number(): void
    {
        while (ctype_digit($this->peek())) {
            $this->current++;
        }
        // Look for a fractional part.
        if ($this->peek() == '.' && ctype_digit($this->peekNext())) {
            // Consume the "."
            $this->current++;

            while (ctype_digit($this->peek())) {
                $this->current++;
            }
        }
        $this->addToken(TokenType::NUMBER, floatval(substr($this->source, $this->start, $this->current - $this->start)));
    }

    private function identifier(): void
    {
        while ($this->isAlphaNumeric($this->peek())) {
            $this->current++;
        }
        $text = substr($this->source, $this->start, $this->current - $this->start);
        $this->addToken(self::$keywords[$text] ?? TokenType::IDENTIFIER);
    }

    private function isAlphaNumeric(string $char): bool
    {
        return ctype_alnum($char) || $char === '_';
    }


}
