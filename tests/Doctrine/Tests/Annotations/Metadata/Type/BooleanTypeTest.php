<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\BooleanType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class BooleanTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new BooleanType();
    }

    public function testDescribe() : void
    {
        self::assertSame('boolean', $this->getType()->describe());
    }

    /**
     * @return bool[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [0];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new stdClass()];
    }

    public function testAcceptsNull() : void
    {
        self::assertFalse($this->getType()->acceptsNull());
    }
}
