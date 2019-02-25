<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\TypeParser;

use Doctrine\Annotations\Metadata\Type\FloatType;
use Doctrine\Annotations\Metadata\Type\IntegerType;
use Doctrine\Annotations\Metadata\Type\IntersectionType;
use Doctrine\Annotations\Metadata\Type\ListType;
use Doctrine\Annotations\Metadata\Type\MapType;
use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Metadata\Type\UnionType;
use Doctrine\Annotations\Parser\Reference\FallbackReferenceResolver;
use Doctrine\Annotations\TypeParser\PHPStanTypeParser;
use Doctrine\Annotations\TypeParser\TypeParser;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser as BaseTypeParser;

final class PHPStanTypeParserTest extends TypeParserTest
{
    protected function createParser() : TypeParser
    {
        return new PHPStanTypeParser(
            new Lexer(),
            new PhpDocParser(new BaseTypeParser(), new ConstExprParser()),
            new FallbackReferenceResolver()
        );
    }

    /**
     * @return string[][]|Type[][]
     */
    public function validPropertyTypesProvider() : iterable
    {
        yield 'empty' => [
            <<<'PHPDOC'
/** */
PHPDOC
        ,
            new MixedType(),
        ];

        yield 'only comment' => [
            <<<'PHPDOC'
/** Hello world */
PHPDOC
        ,
            new MixedType(),
        ];

        yield 'no type' => [
            <<<'PHPDOC'
/** @var */
PHPDOC
        ,
            new MixedType(),
        ];

        yield 'int' => [
            <<<'PHPDOC'
/** @var int */
PHPDOC
        ,
            new IntegerType(),
        ];

        yield 'int with description' => [
            <<<'PHPDOC'
/** @var int Hello world */
PHPDOC
        ,
            new IntegerType(),
        ];

        yield '?int' => [
            <<<'PHPDOC'
/** @var ?int */
PHPDOC
        ,
            new UnionType(new IntegerType(), new NullType()),
        ];

        yield 'int|null' => [
            <<<'PHPDOC'
/** @var int|null */
PHPDOC
        ,
            new UnionType(new IntegerType(), new NullType()),
        ];

        yield 'null|int' => [
            <<<'PHPDOC'
/** @var null|int */
PHPDOC
        ,
            new UnionType(new NullType(), new IntegerType()),
        ];

        yield 'int[]' => [
            <<<'PHPDOC'
/** @var int[] */
PHPDOC
        ,
            new ListType(new IntegerType()),
        ];

        yield 'int[]|null' => [
            <<<'PHPDOC'
/** @var int[]|null */
PHPDOC
        ,
            new UnionType(new ListType(new IntegerType()), new NullType()),
        ];

        yield 'array' => [
            <<<'PHPDOC'
/** @var array */
PHPDOC
            ,
            new MapType(new UnionType(new IntegerType(), new StringType()), new MixedType()),
        ];

        yield 'array<int>' => [
            <<<'PHPDOC'
/** @var array<int> */
PHPDOC
        ,
            new ListType(new IntegerType()),
        ];

        yield 'array<int>|null' => [
            <<<'PHPDOC'
/** @var array<int>|null */
PHPDOC
        ,
            new UnionType(new ListType(new IntegerType()), new NullType()),
        ];

        yield 'array<int, string>' => [
            <<<'PHPDOC'
/** @var array<int, string> */
PHPDOC
        ,
            new MapType(new IntegerType(), new StringType()),
        ];

        yield 'array<int, string>|null' => [
            <<<'PHPDOC'
/** @var array<int, string>|null */
PHPDOC
        ,
            new UnionType(new MapType(new IntegerType(), new StringType()), new NullType()),
        ];

        yield 'int[][]' => [
            <<<'PHPDOC'
/** @var int[][] */
PHPDOC
        ,
            new ListType(new ListType(new IntegerType())),
        ];

        yield 'int[][]|null' => [
            <<<'PHPDOC'
/** @var int[][]|null */
PHPDOC
        ,
            new UnionType(new ListType(new ListType(new IntegerType())), new NullType()),
        ];

        yield 'int[]string[]' => [
            <<<'PHPDOC'
/** @var int[]|string[] */
PHPDOC
        ,
            new UnionType(new ListType(new IntegerType()), new ListType(new StringType())),
        ];

        yield 'SomeType' => [
            <<<'PHPDOC'
/** @var SomeType */
PHPDOC
        ,
            new ObjectType('SomeType'),
        ];

        yield '(SomeClass&SomeInterface)|null' => [
            <<<'PHPDOC'
/** @var (SomeClass&SomeInterface)|null */
PHPDOC
        ,
            new UnionType(
                new IntersectionType(
                    new ObjectType('SomeClass'),
                    new ObjectType('SomeInterface')
                ),
                new NullType()
            ),
        ];

        yield 'SomeClass|(AnotherClass&(AnotherFooInterface|AnotherBarInterface))|null' => [
            <<<'PHPDOC'
/** @var SomeClass|(AnotherClass&(AnotherFooInterface|AnotherBarInterface))|null */
PHPDOC
        ,
            new UnionType(
                new ObjectType('SomeClass'),
                new IntersectionType(
                    new ObjectType('AnotherClass'),
                    new UnionType(
                        new ObjectType('AnotherFooInterface'),
                        new ObjectType('AnotherBarInterface')
                    )
                ),
                new NullType()
            ),
        ];

        yield 'FooBar alias' => [
            <<<'PHPDOC'
/** @var FooBar */
PHPDOC
        ,
            new ObjectType('FooBaz'),
        ];

        yield 'double' => [
            <<<'PHPDOC'
/** @var double */
PHPDOC
        ,
            new FloatType(),
        ];

        yield 'real' => [
            <<<'PHPDOC'
/** @var real */
PHPDOC
        ,
            new FloatType(),
        ];
    }
}
