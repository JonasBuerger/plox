<?php

namespace Plox\Ast\Visitor;

use Plox\Ast\Expression;
use Plox\Ast\ExpressionVisitor;
use Plox\Ast\Node\Binary;
use Plox\Ast\Node\Grouping;
use Plox\Ast\Node\Literal;
use Plox\Ast\Node\Unary;
use Plox\Plox;
use Plox\RuntimeException;
use Plox\Token;
use Plox\TokenType;

/**
 * @template-implements ExpressionVisitor<string|float|bool|null>
 */
class Interpreter implements ExpressionVisitor
{
    public function interpret(Expression $expression): void
    {
        try {
            $value = $expression->accept($this);
            echo strval($value),PHP_EOL;
        } catch (RuntimeException $e) {
            Plox::error($e->getToken(), $e->getMessage());
        }
    }

    public function visitBinary(Binary $binary): string|float|bool|null
    {
        $left = $binary->left->accept($this);
        $right = $binary->right->accept($this);

        switch ($binary->operator->type) {
            case TokenType::MINUS:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left - $right;
            case TokenType::SLASH:
                $this->checkNumberOperands($binary->operator, $left, $right);
                if($right === 0.0){
                    throw new RuntimeException($binary->operator,'Division by zero.');
                }
                return $left / $right;
            case TokenType::STAR:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left * $right;
            case TokenType::PLUS:
                return $this->plus($binary->operator, $left, $right);
            case TokenType::GREATER:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left > $right;
            case TokenType::GREATER_EQUAL:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left >= $right;
            case TokenType::LESS:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left < $right;
            case TokenType::LESS_EQUAL:
                $this->checkNumberOperands($binary->operator, $left, $right);

                return $left <= $right;
            case TokenType::BANG_EQUAL:
                return $left !== $right;
            case TokenType::EQUAL_EQUAL:
                return $left === $right;
            default:
                throw new RuntimeException($binary->operator, 'Not implemented.');
        }
    }

    public function visitGrouping(Grouping $grouping): string|float|bool|null
    {
        return $grouping->expression->accept($this);
    }

    public function visitLiteral(Literal $literal): string|float|bool|null
    {
        return $literal->value;
    }

    public function visitUnary(Unary $unary): string|float|bool
    {
        $value = $unary->right->accept($this);

        switch ($unary->operator->type) {
            case TokenType::BANG:
                return $value === false || $value === null;
            case TokenType::MINUS:
                $this->checkNumberOperands($unary->operator, $value);

                return -$value;
            default:
                throw new RuntimeException($unary->operator, 'Not implemented.');
        }
    }

    private function plus(Token $operator, string|float|bool|null $left, string|float|bool|null $right): string|float
    {
        if (is_float($left) && is_float($right)) {
            return $left + $right;
        }
        if (is_string($left) && is_string($right)) {
            return $left . $right;
        }

        throw new RuntimeException($operator, 'Operands must be two numbers or two strings.');
    }

    private function checkNumberOperands(Token $operator, string|float|bool|null ...$operands): void
    {
        if (array_all($operands, fn (string|float|bool|null $value, $_key): bool => is_float($value))) {
            return;
        }
        throw new RuntimeException($operator, 'Operand must be a number.');
    }
}
