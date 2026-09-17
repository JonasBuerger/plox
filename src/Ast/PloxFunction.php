<?php

namespace Plox\Ast;

use Plox\Ast\Visitor\Interpreter;
use Plox\PloxCallable;

class PloxFunction implements PloxCallable
{
    public function __construct(
        private readonly \Plox\Ast\Node\PloxFunction $declaration,
    )
    {
    }

    /**
	 * @inheritDoc
	 */
	public function call(Interpreter $interpreter, array $arguments): mixed
	{
		// TODO: Implement call() method.
	}

	public function arity(): int
	{
		return count($this->declaration->params);
	}

	/**
	 * @inheritDoc
	 */
	public function __toString()
	{
		// TODO: Implement __toString() method.
	}
}
