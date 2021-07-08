<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint\Exception;

use function gettype;
use function is_scalar;
use function sprintf;

final class InvalidType extends ConstraintNotFulfilled
{
    /**
     * @param mixed $value
     */
    public static function new(string $typeDescription, $value) : self
    {
        // TODO: Describe value
        return new self(
            sprintf(
                'Invalid value "%s" fo type %s.',
                ! is_scalar($value) ? gettype($value) : $value,
                $typeDescription
            )
        );
    }
}
