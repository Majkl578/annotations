<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Metadata\Type\UnionType;

final class TestNullableType
{
    public static function fromType(Type $type) : UnionType
    {
        return new UnionType($type, new NullType());
    }
}
