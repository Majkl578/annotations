<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\InvalidValue;
use function in_array;

final class EnumConstraint implements Constraint
{
    /** @var mixed[] */
    private $allowedValues;

    /**
     * @param mixed[] $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        $this->allowedValues = $allowedValues;
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : void
    {
        if (in_array($value, $this->allowedValues)) {
            return;
        }

        throw InvalidValue::new($this->allowedValues, $value);
    }
}
