<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator;

use Doctrine\Annotations\Assembler\Validator\Constraint\CompositeConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\Constraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\EnumConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\ConstraintNotFulfilled;
use Doctrine\Annotations\Assembler\Validator\Constraint\RequiredConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\TypeConstraint;
use Doctrine\Annotations\Assembler\Validator\Exception\InvalidPropertyValue;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use Doctrine\Annotations\Metadata\Type\Type;
use function count;

final class ValueValidator
{
    /**
     * @param mixed $value
     *
     * @throws InvalidPropertyValue
     */
    public function validate(PropertyMetadata $metadata, $value) : void
    {
        $constraint = $this->determinePropertyConstraint(
            $metadata->type(),
            $metadata->isRequired(),
            $metadata->enumValues()
        );

        try {
            $constraint->validate($value);
        } catch (ConstraintNotFulfilled $exception) {
            throw InvalidPropertyValue::new($metadata, $exception);
        }
    }

    /**
     * @param mixed[] $enumValues
     */
    private function determinePropertyConstraint(Type $type, bool $required, array $enumValues) : Constraint
    {
        $constraints = [new TypeConstraint($type)];

        if ($required) {
            $constraints[] = new RequiredConstraint();
        }

        if (count($enumValues) > 0) {
            $constraints[] = new EnumConstraint($enumValues);
        }

        if (count($constraints) === 1) {
            return $constraints[0];
        }

        return new CompositeConstraint(...$constraints);
    }
}
