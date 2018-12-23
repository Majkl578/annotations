<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations;

use Doctrine\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Annotations\Assembler\Constant\ReflectionConstantResolver;
use Doctrine\Annotations\Assembler\Validator\Exception\InvalidTarget;
use Doctrine\Annotations\Assembler\Validator\Exception\InvalidValue;
use Doctrine\Annotations\Metadata\MetadataCollection;
use Doctrine\Annotations\Metadata\Reflection\DefaultReflectionProvider;
use Doctrine\Annotations\NewAnnotationReader;
use Hoa\Compiler\Exception\UnrecognizedToken;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use TopLevelAnnotation;

class NewAnnotationReaderTest extends TestCase
{
    /** @var MetadataCollection */
    private $collection;

    /** @var NewAnnotationReader */
    private $reader;

    public function setUp() : void
    {
        $this->collection = new MetadataCollection();
        $this->reader     = new NewAnnotationReader(
            $this->collection,
            new DefaultReflectionProvider(),
            new ReflectionConstantResolver(new DefaultReflectionProvider())
        );
    }

    /**
     * @dataProvider classAnnotationExamples
     */
    public function testGetClassAnnotations(string $className, callable $expectedFactory) : void
    {
        $class = new ReflectionClass($className);

        $annotations = $this->reader->getClassAnnotations($class);

        $this->assertEquals($expectedFactory(), $annotations);
    }

    /**
     * @return mixed[]
     */
    public function classAnnotationExamples() : iterable
    {
        yield 'ClassWithAnnotationTargetAll' => [
            Fixtures\ClassWithAnnotationTargetAll::class,
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetAll();
                $annotation->name = '123';

                return [$annotation];
            },
        ];

        yield 'ClassWithValidAnnotationTarget' => [
            Fixtures\ClassWithValidAnnotationTarget::class,
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetClass();
                $annotation->data = 'Some data';

                return [$annotation];
            },
        ];

        yield 'DummyClassWithDanglingComma' => [
            DummyClassWithDanglingComma::class,
            static function () : array {
                return [new DummyAnnotation(['dummyValue' => 'bar'])];
            },
        ];

        yield 'ClassWithRequire' => [
            Fixtures\ClassWithRequire::class,
            static function () : array {
                return [new DummyAnnotationWithIgnoredAnnotation(['dummyValue' => 'hello'])];
            },
        ];

        yield 'TestIgnoresNonAnnotationsClass' => [
            TestIgnoresNonAnnotationsClass::class,
            static function () : array {
                return [new Name([])];
            },
        ];

        yield 'InvalidAnnotationUsageButIgnoredClass' => [
            Fixtures\InvalidAnnotationUsageButIgnoredClass::class,
            static function () : array {
                $routeAnnotation       = new Fixtures\Annotation\Route();
                $routeAnnotation->name = 'foo';

                return [
                    new IgnoreAnnotation(['NoAnnotation']),
                    $routeAnnotation,
                ];
            },
        ];

        yield 'InvalidAnnotationButIgnored - DDC-1660' => [
            Fixtures\ClassDDC1660::class,
            static function () : array {
                return [];
            },
        ];

        yield 'ignore case insensitivity - DCOM-106' => [
            DCOM106::class,
            static function () : array {
                return [];
            },
        ];
    }

    /**
     * @dataProvider invalidClassAnnotationExamples
     */
    public function testGetInvalidClassAnnotations(string $className, string $expectedException) : void
    {
        $class = new ReflectionClass($className);

        $this->expectException($expectedException);

        $this->reader->getClassAnnotations($class);
    }

    /**
     * @return mixed[]
     */
    public function invalidClassAnnotationExamples() : iterable
    {
        yield 'ClassWithAnnotationTargetAll' => [
            Fixtures\ClassWithInvalidAnnotationTargetAtClass::class,
            InvalidTarget::class,
        ];

        yield 'ClassWithAnnotationWithTargetSyntaxError' => [
            Fixtures\ClassWithAnnotationWithTargetSyntaxError::class,
            UnrecognizedToken::class,
        ];

        yield 'DummyClassSyntaxError' => [
            DummyClassSyntaxError::class,
            UnrecognizedToken::class,
        ];

        yield 'InvalidAnnotationUsageClass' => [
            Fixtures\InvalidAnnotationUsageClass::class,
            UnrecognizedToken::class,
        ];
    }

    /**
     * @dataProvider methodAnnotationExamples
     */
    public function testGetMethodAnnotations(string $className, string $methodName, callable $expectedFactory) : void
    {
        $method = new ReflectionMethod($className, $methodName);

        $annotations = $this->reader->getMethodAnnotations($method);

        $this->assertEquals($expectedFactory(), $annotations);
    }

    /**
     * @return mixed[]
     */
    public function methodAnnotationExamples() : iterable
    {
        yield 'ClassWithAnnotationWithVarType - bar' => [
            Fixtures\ClassWithAnnotationWithVarType::class,
            'bar',
            static function () : array {
                $annotation             = new Fixtures\AnnotationWithVarType();
                $annotation->annotation = new Fixtures\AnnotationTargetAll();

                return [$annotation];
            },
        ];

        yield 'InvalidAnnotationButIgnored - DDC-1660 - bar' => [
            Fixtures\ClassDDC1660::class,
            'bar',
            static function () : array {
                return [];
            },
        ];
    }

    /**
     * @dataProvider invalidMethodAnnotationExamples
     */
    public function testGetInvalidMethodAnnotations(string $className, string $methodName, string $expectedException) : void
    {
        $method = new ReflectionMethod($className, $methodName);

        $this->expectException($expectedException);

        $this->reader->getMethodAnnotations($method);
    }

    /**
     * @return mixed[]
     */
    public function invalidMethodAnnotationExamples() : iterable
    {
        yield 'ClassWithInvalidAnnotationTargetAtMethod - functionName' => [
            Fixtures\ClassWithInvalidAnnotationTargetAtMethod::class,
            'functionName',
            InvalidTarget::class,
        ];

        yield 'ClassWithAnnotationWithTargetSyntaxError - bar' => [
            Fixtures\ClassWithAnnotationWithTargetSyntaxError::class,
            'bar',
            UnrecognizedToken::class,
        ];

        yield 'ClassWithAnnotationWithVarType - invalidMethod' => [
            Fixtures\ClassWithAnnotationWithVarType::class,
            'invalidMethod',
            InvalidValue::class,
        ];

        yield 'DummyClassMethodSyntaxError - foo' => [
            DummyClassMethodSyntaxError::class,
            'foo',
            UnrecognizedToken::class,
        ];

        yield 'ClassWithAnnotationEnum - invalid value - bar' => [
            Fixtures\ClassWithAnnotationEnum::class,
            'bar',
            InvalidValue::class,
        ];
    }

    /**
     * @dataProvider propertyAnnotationExamples
     */
    public function testGetPropertyAnnotations(string $className, string $propertyName, callable $expectedFactory) : void
    {
        $property = new ReflectionProperty($className, $propertyName);

        $annotations = $this->reader->getPropertyAnnotations($property);

        $this->assertEquals($expectedFactory(), $annotations);
    }

    /**
     * @return mixed[]
     */
    public function propertyAnnotationExamples() : iterable
    {
        yield 'ClassWithValidAnnotationTarget - foo' => [
            Fixtures\ClassWithValidAnnotationTarget::class,
            'foo',
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetPropertyMethod();
                $annotation->data = 'Some data';

                return [$annotation];
            },
        ];

        yield 'ClassWithValidAnnotationTarget - name' => [
            Fixtures\ClassWithValidAnnotationTarget::class,
            'name',
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetAll();
                $annotation->data = 'Some data';
                $annotation->name = 'Some name';

                return [$annotation];
            },
        ];

        yield 'ClassWithAnnotationWithVarType - bar' => [
            Fixtures\ClassWithAnnotationWithVarType::class,
            'foo',
            static function () : array {
                $annotation         = new Fixtures\AnnotationWithVarType();
                $annotation->string = 'String Value';

                return [$annotation];
            },
        ];

        yield 'ClassWithAtInDescriptionAndAnnotation - foo' => [
            Fixtures\ClassWithAtInDescriptionAndAnnotation::class,
            'foo',
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetPropertyMethod();
                $annotation->data = 'Bar';

                return [$annotation];
            },
        ];

        yield 'ClassWithAtInDescriptionAndAnnotation - bar' => [
            Fixtures\ClassWithAtInDescriptionAndAnnotation::class,
            'bar',
            static function () : array {
                $annotation       = new Fixtures\AnnotationTargetPropertyMethod();
                $annotation->data = 'Bar';

                return [$annotation];
            },
        ];

        yield 'DummyClass2 - id - multiple annotations on the same line' => [
            DummyClass2::class,
            'id',
            static function () : array {
                return [
                    new DummyId([]),
                    new DummyColumn(['type' => 'integer']),
                    new DummyGeneratedValue([]),
                ];
            },
        ];

        yield 'DummyClassNonAnnotationProblem - foo' => [
            DummyClassNonAnnotationProblem::class,
            'foo',
            static function () : array {
                return [new DummyAnnotation([])];
            },
        ];

        yield 'TestParentClass - child' => [
            TestParentClass::class,
            'child',
            static function () : array {
                return [new Foo\Name(['name' => 'foo']),
                ];
            },
        ];

        yield 'TestParentClass - parent' => [
            TestParentClass::class,
            'parent',
            static function () : array {
                return [new Bar\Name(['name' => 'bar']),
                ];
            },
        ];

        yield 'TestTopLevelAnnotationClass - field' => [
            TestTopLevelAnnotationClass::class,
            'field',
            static function () : array {
                return [new TopLevelAnnotation([])];
            },
        ];

        yield 'InvalidAnnotationButIgnored - DDC-1660 - foo' => [
            Fixtures\ClassDDC1660::class,
            'foo',
            static function () : array {
                return [];
            },
        ];
    }

    /**
     * @dataProvider invalidPropertyAnnotationExamples
     */
    public function testGetInvalidPropertyAnnotations(string $className, string $methodName, string $expectedException) : void
    {
        $property = new ReflectionProperty($className, $methodName);

        $this->expectException($expectedException);

        $this->reader->getPropertyAnnotations($property);
    }

    /**
     * @return mixed[]
     */
    public function invalidPropertyAnnotationExamples() : iterable
    {
        yield 'ClassWithInvalidAnnotationTargetAtProperty - foo' => [
            Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class,
            'foo',
            InvalidTarget::class,
        ];

        yield 'ClassWithInvalidAnnotationTargetAtProperty - bar' => [
            Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class,
            'bar',
            InvalidTarget::class,
        ];

        yield 'ClassWithAnnotationWithTargetSyntaxError - foo' => [
            Fixtures\ClassWithAnnotationWithTargetSyntaxError::class,
            'foo',
            UnrecognizedToken::class,
        ];

        yield 'ClassWithAnnotationWithVarType - invalidProperty' => [
            Fixtures\ClassWithAnnotationWithVarType::class,
            'invalidProperty',
            InvalidValue::class,
        ];

        yield 'DummyClassPropertySyntaxError - foo' => [
            DummyClassPropertySyntaxError::class,
            'foo',
            UnrecognizedToken::class,
        ];

        yield 'TestAnnotationNotImportedClass - field' => [
            TestAnnotationNotImportedClass::class,
            'field',
            ReflectionException::class,
        ];

        yield 'TestNonExistentAnnotationClass - field' => [
            TestNonExistentAnnotationClass::class,
            'field',
            ReflectionException::class,
        ];

        yield 'ClassWithAnnotationEnum - invalid value - foo' => [
            Fixtures\ClassWithAnnotationEnum::class,
            'foo',
            InvalidValue::class,
        ];
    }
}
