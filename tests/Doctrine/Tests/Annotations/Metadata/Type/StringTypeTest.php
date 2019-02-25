<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class StringTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new StringType();
    }

    public function testDescribe() : void
    {
        self::assertSame('string', $this->getType()->describe());
    }

    /**
     * @return string[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [''];
        yield ['123'];
        yield ['hello'];
        yield ['ěščřžýáíé'];
        yield ['😊'];
        yield ["\0"];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [123];
        yield [1.234];
        yield [[123]];
        yield [new stdClass()];
    }

    public function testAcceptsNull() : void
    {
        self::assertFalse($this->getType()->acceptsNull());
    }
}
