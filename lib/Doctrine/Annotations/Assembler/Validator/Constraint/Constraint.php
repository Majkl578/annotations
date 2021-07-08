<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\ConstraintNotFulfilled;

interface Constraint
{
    /**
     * @param mixed $value
     *
     * @throws ConstraintNotFulfilled
     */
    public function validate($value) : void;
}
