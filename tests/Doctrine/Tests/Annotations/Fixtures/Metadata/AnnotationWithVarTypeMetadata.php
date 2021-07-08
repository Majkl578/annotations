<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Fixtures\Metadata;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Assembler\Validator\Constraint\TypeConstraint;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use Doctrine\Annotations\Metadata\Type\BooleanType;
use Doctrine\Annotations\Metadata\Type\FloatType;
use Doctrine\Annotations\Metadata\Type\IntegerType;
use Doctrine\Annotations\Metadata\Type\ListType;
use Doctrine\Annotations\Metadata\Type\MapType;
use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\UnionType;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithVarType;

final class AnnotationWithVarTypeMetadata
{
    public static function get(): AnnotationMetadata
    {
        return new AnnotationMetadata(
            AnnotationWithVarType::class,
            new AnnotationTarget(AnnotationTarget::TARGET_ALL),
            false,
            [
                new PropertyMetadata(
                    'mixed',
                    new MixedType(),
                    true
                ),
                new PropertyMetadata(
                    'boolean',
                    new BooleanType()
                ),
                new PropertyMetadata(
                    'bool',
                    new BooleanType()
                ),
                new PropertyMetadata(
                    'float',
                    new FloatType()
                ),
                new PropertyMetadata(
                    'string',
                    new StringType()
                ),
                new PropertyMetadata(
                    'integer',
                    new IntegerType()
                ),
                new PropertyMetadata(
                    'array',
                    new MapType(new UnionType(new IntegerType(), new StringType()), new MixedType())
                ),
                new PropertyMetadata(
                    'arrayMap',
                    new MapType(new StringType(), new MixedType())
                ),
                new PropertyMetadata(
                    'annotation',
                    new ObjectType(AnnotationTargetAll::class)
                ),
                new PropertyMetadata(
                    'arrayOfIntegers',
                    new ListType(new IntegerType())
                ),
                new PropertyMetadata(
                    'arrayOfStrings',
                    new ListType(new StringType())
                ),
                new PropertyMetadata(
                    'arrayOfAnnotations',
                    new ListType(new ObjectType(AnnotationTargetAll::class))
                ),
            ]
        );
    }
}
