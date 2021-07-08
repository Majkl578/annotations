<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\ConstraintNotFulfilled;
use Doctrine\Annotations\Assembler\Validator\Constraint\RequiredConstraint;
use PHPUnit\Framework\TestCase;

final class RequiredConstraintTest extends TestCase
{
    public function testValidatesNotNullValue() : void
    {
        $constraint = new RequiredConstraint();

        $constraint->validate('foo');

        self::assertTrue(true);
    }

    public function testValidatesNullValueAndThrows() : void
    {
        $constraint = new RequiredConstraint();

        $this->expectException(ConstraintNotFulfilled::class);

        $constraint->validate(null);
    }
}
