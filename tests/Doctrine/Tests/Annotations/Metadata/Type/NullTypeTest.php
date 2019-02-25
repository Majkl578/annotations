<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class NullTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new NullType();
    }

    public function testDescribe() : void
    {
        self::assertSame('null', $this->getType()->describe());
    }

    /**
     * @return null[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [null];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [0];
        yield [false];
        yield [0.0];
        yield [''];
        yield [123];
        yield [[]];
        yield [new stdClass()];
    }

    public function testAcceptsNull() : void
    {
        self::assertTrue($this->getType()->acceptsNull());
    }
}
