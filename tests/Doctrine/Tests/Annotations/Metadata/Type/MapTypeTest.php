<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\MapType;
use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\ScalarType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;

final class MapTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new MapType($this->getKeyType(), $this->getValueType());
    }

    public function testDescribe() : void
    {
        self::assertSame('array<string, mixed>', $this->getType()->describe());
    }

    /**
     * @return mixed[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [
            ['foo' => 'bar'],
            ['baz' => 42],
            ['woof' => new stdClass()],
            ['meow' => null],
            [
                'multiple' => 1,
                'items' => static function () : void {
                },
                'with' => new class () {
                },
                'different' => null,
                'types' =>  'test',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [
            ['foo', 1],
            [1 => 'bar'],
            ['baz' => new stdClass(), 1 => 'zaz'],
        ];
    }

    public function testAcceptsNull() : void
    {
        self::assertSame($this->getValueType()->acceptsNull(), $this->getType()->acceptsNull());
    }

    private function getKeyType() : ScalarType
    {
        return new StringType();
    }

    private function getValueType() : Type
    {
        return new MixedType();
    }
}
