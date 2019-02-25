<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\FloatType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class FloatTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new FloatType();
    }

    public function testDescribe() : void
    {
        self::assertSame('float', $this->getType()->describe());
    }

    /**
     * @return float[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [0.0];
        yield [123e-456];
        yield [1.234];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [123];
        yield ['0.0'];
        yield [[0.0]];
        yield [new stdClass()];
    }

    public function testAcceptsNull() : void
    {
        self::assertFalse($this->getType()->acceptsNull());
    }
}
