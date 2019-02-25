<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use DateTimeImmutable;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class ObjectTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new ObjectType('stdClass');
    }

    public function testDescribe() : void
    {
        self::assertSame(stdClass::class, $this->getType()->describe());
    }

    /**
     * @return object[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [new stdClass()];
        yield [
            new class extends stdClass {
            },
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [true];
        yield [0];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new DateTimeImmutable()];
    }

    public function testAcceptsNull() : void
    {
        self::assertFalse($this->getType()->acceptsNull());
    }
}
