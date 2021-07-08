<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Tests\Annotations\AnnotationMetadataBuilder;

final class AnnotationMetadataMother
{
    public static function example() : AnnotationMetadata
    {
        return (new AnnotationMetadataBuilder())
            ->build();
    }

    public static function withTarget(AnnotationTarget $target) : AnnotationMetadata
    {
        return (new AnnotationMetadataBuilder())
            ->withTarget($target)
            ->build();
    }
}
