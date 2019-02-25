<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\IntersectionType;
use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\Type;
use IteratorAggregate;
use stdClass;

final class IntersectionTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new IntersectionType(new ObjectType(stdClass::class), new ObjectType(IteratorAggregate::class));
    }

    public function testDescribe() : void
    {
        self::assertSame('stdClass&IteratorAggregate', $this->getType()->describe());
    }

    /**
     * @return object[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [
            new class extends stdClass implements IteratorAggregate {
                /**
                 * @return int[]
                 */
                public function getIterator() : iterable
                {
                    yield 123;
                }
            },
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [true];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new stdClass()];
        yield [
            new class implements IteratorAggregate {
                /**
                 * @return int[]
                 */
                public function getIterator() : iterable
                {
                    yield 123;
                }
            },
        ];
    }

    public function testNotAcceptsNullStoringNotNullAcceptingType() : void
    {
        self::assertFalse($this->getType()->acceptsNull());
    }

    public function testAcceptsNull() : void
    {
        $type = new IntersectionType(new NullType());

        self::assertTrue($type->acceptsNull());
    }
}
