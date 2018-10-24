<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Constraint;

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
                $value,
                $typeDescription
            )
        );
    }
}
