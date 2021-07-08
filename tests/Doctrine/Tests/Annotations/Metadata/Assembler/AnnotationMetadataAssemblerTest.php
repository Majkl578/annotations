<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Assembler;

use Doctrine\Annotations\Assembler\Acceptor\InternalAcceptor;
use Doctrine\Annotations\Assembler\Assembler;
use Doctrine\Annotations\Assembler\Constant\ReflectionConstantResolver;
use Doctrine\Annotations\Constructor\Constructor;
use Doctrine\Annotations\Constructor\Instantiator\ConstructorInstantiatorStrategy;
use Doctrine\Annotations\Constructor\Instantiator\Instantiator;
use Doctrine\Annotations\Constructor\Instantiator\PropertyInstantiatorStrategy;
use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\Assembler\AnnotationMetadataAssembler;
use Doctrine\Annotations\Metadata\InternalAnnotations;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use Doctrine\Annotations\Metadata\Reflection\DefaultReflectionProvider;
use Doctrine\Annotations\Metadata\ScopeManufacturer;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Compiler;
use Doctrine\Annotations\Parser\IgnoredAnnotations;
use Doctrine\Annotations\Parser\Imports;
use Doctrine\Annotations\Parser\Reference\FallbackReferenceResolver;
use Doctrine\Annotations\Parser\Reference\StaticReferenceResolver;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Annotations\PhpParser;
use Doctrine\Annotations\TypeParser\PHPStanTypeParser;
use Doctrine\Tests\Annotations\Fixtures\AnnotationEnum;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAnnotation;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithConstants;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithRequiredAttributes;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithVarType;
use Doctrine\Tests\Annotations\Fixtures\Metadata\AnnotationEnumMetadata;
use Doctrine\Tests\Annotations\Fixtures\Metadata\AnnotationTargetAllMetadata;
use Doctrine\Tests\Annotations\Fixtures\Metadata\AnnotationWithConstantsMetadata;
use Doctrine\Tests\Annotations\Fixtures\Metadata\AnnotationWithVarTypeMetadata;
use Doctrine\Tests\Annotations\Metadata\Type\TestNullableType;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnnotationMetadataAssemblerTest extends TestCase
{
    /** @var Compiler */
    private $compiler;

    /** @var PhpParser */
    private $phpParser;

    /** @var AnnotationMetadataAssembler */
    private $metadataAssembler;

    /** @var Constructor */
    private $constructor;

    public function setUp() : void
    {
        $this->compiler          = new Compiler();
        $this->phpParser         = new PhpParser();
        $this->constructor       = new Constructor(
            new Instantiator(
                new ConstructorInstantiatorStrategy(),
                new PropertyInstantiatorStrategy()
            )
        );
        $this->metadataAssembler = new AnnotationMetadataAssembler(
            $this->compiler,
            new FallbackReferenceResolver(),
            new DefaultReflectionProvider(),
            new PHPStanTypeParser(new Lexer(), new PhpDocParser(new TypeParser(), new ConstExprParser()), new FallbackReferenceResolver()),
            new ScopeManufacturer($this->phpParser),
            new Assembler(
                InternalAnnotations::createMetadata(),
                new StaticReferenceResolver(),
                $this->constructor,
                new DefaultReflectionProvider(),
                new InternalAcceptor(
                    new StaticReferenceResolver()
                ),
                new ReflectionConstantResolver(new DefaultReflectionProvider())
            )
        );
    }

    /**
     * @dataProvider validExamples
     */
    public function testAssemblingValidExamples(Reference $reference, Scope $scope, callable $asserter) : void
    {
        $metadata = $this->metadataAssembler->assemble($reference, $scope);

        $asserter($metadata);
    }

    /**
     * @return mixed[]
     */
    public function validExamples() : iterable
    {
        yield 'fixture - AnnotationTargetAll' => [
            new Reference(AnnotationTargetAll::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            function (AnnotationMetadata $metadata) : void {
                $this->assertEquals(AnnotationTargetAllMetadata::get(), $metadata);
            },
        ];

        yield 'fixture - AnnotationWithRequiredAttributes' => [
            new Reference(AnnotationWithRequiredAttributes::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            function (AnnotationMetadata $metadata) : void {
                $this->assertSame(AnnotationWithRequiredAttributes::class, $metadata->getName());
                $this->assertTrue($metadata->getTarget()->all(), 'Invalid target');
                $this->assertTrue($metadata->hasConstructor(), 'Has no constructor');
                $properties = $metadata->getProperties();
                $this->assertEmpty($properties);
                $this->assertNull($metadata->getDefaultProperty());
            },
        ];

        yield 'fixture - AnnotationWithRequiredAttributesWithoutConstructor' => [
            new Reference(AnnotationWithRequiredAttributesWithoutConstructor::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            function (AnnotationMetadata $metadata) : void {
                $this->assertSame(AnnotationWithRequiredAttributesWithoutConstructor::class, $metadata->getName());
                $this->assertTrue($metadata->getTarget()->all(), 'Invalid target');
                $this->assertFalse($metadata->hasConstructor(), 'Has constructor');
                $properties         = $metadata->getProperties();
                $expectedProperties = [
                    'value' => new PropertyMetadata(
                        'value',
                        TestNullableType::fromType(new StringType()),
                        true,
                        [],
                        true
                    ),
                    'annot' => new PropertyMetadata(
                        'annot',
                        TestNullableType::fromType(new ObjectType(AnnotationTargetAnnotation::class)),
                        false,
                        [],
                        true
                    ),
                ];

                $this->assertEquals(
                    $expectedProperties,
                    $properties
                );
                $this->assertEquals(
                    $expectedProperties['value'],
                    $metadata->getDefaultProperty()
                );
            },
        ];

        yield 'fixture - AnnotationWithVarType' => [
            new Reference(AnnotationWithVarType::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            function (AnnotationMetadata $metadata) : void {
                $this->assertEquals(AnnotationWithVarTypeMetadata::get(), $metadata);
            },
        ];

        yield 'fixture - AnnotationWithConstants' => [
            new Reference(AnnotationWithConstants::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            function (AnnotationMetadata $metadata) : void {
                $this->assertEquals(AnnotationWithConstantsMetadata::get(), $metadata);
            },
        ];

        yield 'fixture - AnnotationEnum' => [
            new Reference(AnnotationEnum::class, true),
            new Scope(new ReflectionClass($this), new Imports([]), new IgnoredAnnotations()),
            static function (AnnotationMetadata $metadata) : void {
                self::assertEquals(AnnotationEnumMetadata::get(), $metadata);
            },
        ];
    }
}
