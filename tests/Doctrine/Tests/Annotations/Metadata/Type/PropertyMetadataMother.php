<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\PropertyMetadata;
use Doctrine\Annotations\Metadata\Type\Type;

final class PropertyMetadataMother
{
    public static function withType(Type $type) : PropertyMetadata
    {
        return new PropertyMetadata('foo', $type);
    }
}
