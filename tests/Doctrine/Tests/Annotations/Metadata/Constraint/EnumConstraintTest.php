<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\EnumConstraint;
use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\InvalidValue;
use PHPUnit\Framework\TestCase;

final class EnumConstraintTest extends TestCase
{
    /**
     * @param mixed[] $allowedValues
     * @param mixed   $value
     *
     * @dataProvider fulfilledProvider
     */
    public function testFulfilledByGivenValue(array $allowedValues, $value) : void
    {
        $constraint = new EnumConstraint($allowedValues);

        $constraint->validate($value);

        self::assertTrue(true);
    }

    /**
     * @return mixed[]
     */
    public function fulfilledProvider() : iterable
    {
        yield 'matching string' => [
            ['foo', 'bar'],
            'bar',
        ];
    }

    /**
     * @param mixed[] $allowedValues
     * @param mixed   $value
     *
     * @dataProvider notFulfilledProvider
     */
    public function testNotFulfilledByGivenValue(array $allowedValues, $value) : void
    {
        $constraint = new EnumConstraint($allowedValues);

        $this->expectException(InvalidValue::class);

        $constraint->validate($value);
    }

    /**
     * @return mixed[]
     */
    public function notFulfilledProvider() : iterable
    {
        yield 'not matching string' => [
            ['foo', 'bar'],
            'baz',
        ];
    }
}
