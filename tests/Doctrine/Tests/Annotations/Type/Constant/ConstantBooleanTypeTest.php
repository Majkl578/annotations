<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\ConstantBooleanType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Tests\Annotations\Type\TypeTest;
use stdClass;

final class ConstantBooleanTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new ConstantBooleanType(true);
    }

    public function getDescription() : string
    {
        return 'true';
    }

    public function validValidateValuesProvider() : iterable
    {
        yield [true];
    }

    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [1];
        yield [1.23];
        yield ['123'];
        yield [[123]];
        yield [new stdClass()];
    }
}
