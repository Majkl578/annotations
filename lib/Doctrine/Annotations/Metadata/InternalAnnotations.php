<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @internal
 */
final class InternalAnnotations
{
    /**
     * @return string[]
     */
    public static function getNames() : iterable
    {
        yield Enum::class;
        yield IgnoreAnnotation::class;
        yield Required::class;
        yield Target::class;
    }

    public static function createMetadata() : MetadataCollection
    {
        return new MetadataCollection(
            new AnnotationMetadata(
                Attribute::class,
                AnnotationTarget::annotation(),
                false,
                new PropertyMetadata(
                    'name',
                    ['type' => 'string'],
                    true,
                    true
                ),
                new PropertyMetadata(
                    'type',
                    ['type' => 'string'],
                    true
                ),
                new PropertyMetadata(
                    'required',
                    ['type' => 'boolean']
                )
            ),
            new AnnotationMetadata(
                Attributes::class,
                AnnotationTarget::class(),
                false,
                new PropertyMetadata(
                    'value',
                    [
                        'type'       => 'array',
                        'array_type' =>'Doctrine\Common\Annotations\Annotation\Attribute',
                        'value'      =>'array<Doctrine\Common\Annotations\Annotation\Attribute>',
                    ],
                    true,
                    true
                )
            ),
            new AnnotationMetadata(
                Enum::class,
                AnnotationTarget::property(),
                true,
                new PropertyMetadata(
                    'value',
                    ['type' => 'array'],
                    true,
                    true
                ),
                new PropertyMetadata(
                    'literal',
                    ['type' => 'array']
                )
            ),
            new AnnotationMetadata(
                Target::class,
                AnnotationTarget::class(),
                true,
                new PropertyMetadata(
                    'value',
                    [
                        'type'      =>'array',
                        'array_type'=>'string',
                        'value'     =>'array<string>',
                    ],
                    false,
                    true
                )
            )
        );
    }
}
