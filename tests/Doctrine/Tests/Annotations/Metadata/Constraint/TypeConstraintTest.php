<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Constraint;

use Doctrine\Annotations\Annotation;
use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\ConstraintNotFulfilled;
use Doctrine\Annotations\Assembler\Validator\Constraint\TypeConstraint;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAll;
use PHPUnit\Framework\TestCase;

final class TypeConstraintTest extends TestCase
{
    /**
     * @param mixed $value
     *
     * @dataProvider matchingProvider
     */
    public function testValidatesValueMatchingType(Type $type, $value) : void
    {
        $constraint = new TypeConstraint($type);

        $constraint->validate($value);

        self::assertTrue(true);
    }

    /**
     * @return mixed[]
     */
    public function matchingProvider() : iterable
    {
        yield 'a string' => [
            new StringType(),
            'foo',
        ];

        yield 'an object' => [
            new ObjectType(Annotation::class),
            new Annotation([]),
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider notMatchingProvider
     */
    public function testValidatesValueNotMatchingTypeAndThrows(Type $type, $value) : void
    {
        $constraint = new TypeConstraint($type);

        $this->expectException(ConstraintNotFulfilled::class);

        $constraint->validate($value);
    }

    /**
     * @return mixed[]
     */
    public function notMatchingProvider() : iterable
    {
        yield 'not a string' => [
            new StringType(),
            42,
        ];

        yield 'a different object' => [
            new ObjectType(AnnotationTargetAll::class),
            new Annotation([]),
        ];
    }
}
