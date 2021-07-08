<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint\Exception;

use function gettype;
use function implode;
use function sprintf;

final class InvalidValue extends ConstraintNotFulfilled
{
    /**
     * @param mixed[] $allowedValues
     * @param mixed   $value
     */
    public static function new(array $allowedValues, $value) : self
    {
        // TODO: Describe values
        return new self(
            sprintf(
                'Invalid value "%s" for allowed values: "%s".',
                gettype($value),
                implode(',', $allowedValues)
            )
        );
    }
}
