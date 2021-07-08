<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint\Exception;

use Doctrine\Annotations\Assembler\Validator\Exception\ValidationException;
use Exception;
use Throwable;

abstract class ConstraintNotFulfilled extends Exception implements ValidationException
{
    protected function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
