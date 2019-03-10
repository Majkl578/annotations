<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\MapType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\ScalarType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\Type;
use stdClass;

final class MapTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new MapType($this->getKeyType(), $this->getValueType());
    }

    public function getDescription() : string
    {
        return 'array<string, mixed>';
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

    private function getKeyType() : ScalarType
    {
        return new StringType();
    }

    private function getValueType() : Type
    {
        return new MixedType();
    }
}
