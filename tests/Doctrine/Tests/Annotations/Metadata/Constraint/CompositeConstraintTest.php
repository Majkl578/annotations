<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\CompositeConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\ConstraintNotFulfilled;
use Doctrine\Annotations\Assembler\Validator\Constraint\RequiredConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\TypeConstraint;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Tests\Annotations\Metadata\Type\TestNullableType;
use PHPUnit\Framework\TestCase;

final class CompositeConstraintTest extends TestCase
{
    public function testCombinesMultipleConstraintAndPassesWhenAllAreFulfilled() : void
    {
        $constraint = new CompositeConstraint(
            new TypeConstraint(TestNullableType::fromType(new StringType())),
            new RequiredConstraint()
        );

        $constraint->validate('foo');

        $this->expectException(ConstraintNotFulfilled::class);

        $constraint->validate(null);
    }
}
