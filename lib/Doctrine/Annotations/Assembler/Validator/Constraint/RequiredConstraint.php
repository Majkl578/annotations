<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\MissingRequiredValue;

final class RequiredConstraint implements Constraint
{
    /**
     * @param mixed $value
     */
    public function validate($value) : void
    {
        if ($value === null) {
            throw MissingRequiredValue::new();
        }
    }
}
