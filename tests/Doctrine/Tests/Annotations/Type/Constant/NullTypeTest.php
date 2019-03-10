<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\NullType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Tests\Annotations\Type\TypeTest;
use stdClass;

final class NullTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new NullType();
    }

    public function getDescription() : string
    {
        return 'null';
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
}
