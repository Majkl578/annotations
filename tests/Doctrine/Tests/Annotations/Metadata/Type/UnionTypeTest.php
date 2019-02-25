<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use DateTimeImmutable;
use Doctrine\Annotations\Metadata\Type\IntegerType;
use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Metadata\Type\UnionType;
use stdClass;

final class UnionTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new UnionType(new IntegerType(), new ObjectType(stdClass::class), new NullType());
    }

    public function testDescribe() : void
    {
        self::assertSame('integer|stdClass|null', $this->getType()->describe());
    }

    /**
     * @return null[][]|object[][]|int[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [null];
        yield [new stdClass()];
        yield [123];
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
        yield [new DateTimeImmutable()];
    }

    public function testAcceptsNull() : void
    {
        self::assertTrue($this->getType()->acceptsNull());
    }

    public function testNotAcceptsNullStoringNotNullAcceptingType() : void
    {
        $type = new UnionType(new StringType());

        self::assertFalse($type->acceptsNull());
    }
}
