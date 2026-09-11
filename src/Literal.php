<?php

namespace Plox;

class Literal extends Expression
{
public function __construct(
    public Token $token,
)
{
}
}
