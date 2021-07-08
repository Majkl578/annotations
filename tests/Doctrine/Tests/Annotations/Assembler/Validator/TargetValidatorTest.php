<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Assembler\Validator;

use Doctrine\Annotations\Assembler\Validator\Exception\InvalidTarget;
use Doctrine\Annotations\Assembler\Validator\TargetValidator;
use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Tests\Annotations\Metadata\AnnotationMetadataMother;
use Doctrine\Tests\Annotations\Parser\ScopeMother;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;
use function get_class;
use function sprintf;

final class TargetValidatorTest extends TestCase
{
    /** @var TargetValidator */
    private $validator;

    protected function setUp() : void
    {
        $this->validator = new TargetValidator();
    }

    /**
     * @dataProvider targetsWithMatchingScopeProvider
     */
    public function testValidatesTargetForMatchingScope(AnnotationTarget $target, Scope $scope) : void
    {
        $metadata = AnnotationMetadataMother::withTarget($target);

        $this->validator->validate($metadata, $scope);

        self::assertTrue(true);
    }

    /**
     * @return mixed[]
     */
    public function targetsWithMatchingScopeProvider() : iterable
    {
        /** @var Reflector[] $reflectors */
        $reflectors = [
            new ReflectionClass(self::class),
            new ReflectionProperty(AnnotationMetadata::class, 'name'),
            new ReflectionMethod(self::class, 'setUp'),
        ];

        foreach ($reflectors as $reflector) {
            yield 'TargetAll for reflector ' . get_class($reflector) => [
                new AnnotationTarget(AnnotationTarget::TARGET_ALL),
                ScopeMother::withSubject($reflector),
            ];
        }

        foreach ([AnnotationTarget::TARGET_ALL, AnnotationTarget::TARGET_ANNOTATION] as $target) {
            yield sprintf('Target of value %d for nested scope', $target) => [
                new AnnotationTarget($target),
                ScopeMother::withNestingLevel(2),
            ];
        }

        $matchingReflectors = [
            AnnotationTarget::TARGET_CLASS => $reflectors[0],
            AnnotationTarget::TARGET_PROPERTY => $reflectors[1],
            AnnotationTarget::TARGET_METHOD => $reflectors[2],
        ];

        foreach ($matchingReflectors as $target => $reflector) {
            yield sprintf('Target of value %d for reflector %s', $target, get_class($reflector)) => [
                new AnnotationTarget($target),
                ScopeMother::withSubject($reflector),
            ];

            yield sprintf('Target of value %d for reflector %s', AnnotationTarget::TARGET_ALL, get_class($reflector)) => [
                new AnnotationTarget(AnnotationTarget::TARGET_ALL),
                ScopeMother::withSubject($reflector),
            ];
        }
    }

    /**
     * @dataProvider targetsWithNotMatchingScopeProvider
     */
    public function testValidatesTargetForNotMatchingScopeAndThrows(AnnotationTarget $target, Scope $scope) : void
    {
        $metadata = AnnotationMetadataMother::withTarget($target);

        $this->expectException(InvalidTarget::class);

        $this->validator->validate($metadata, $scope);
    }

    /**
     * @return mixed[]
     */
    public function targetsWithNotMatchingScopeProvider() : iterable
    {
        foreach ([AnnotationTarget::TARGET_CLASS, AnnotationTarget::TARGET_METHOD, AnnotationTarget::TARGET_PROPERTY] as $target) {
            yield sprintf('Target of value %d for nested scope', $target) => [
                new AnnotationTarget($target),
                ScopeMother::withNestingLevel(2),
            ];
        }

        $notMatchingReflectors = [
            AnnotationTarget::TARGET_CLASS => new ReflectionMethod(self::class, 'setUp'),
            AnnotationTarget::TARGET_METHOD => new ReflectionProperty(AnnotationMetadata::class, 'name'),
            AnnotationTarget::TARGET_PROPERTY => new ReflectionClass(self::class),
        ];

        foreach ($notMatchingReflectors as $target => $reflector) {
            yield sprintf('Target of value %d for reflector %s', $target, get_class($reflector)) => [
                new AnnotationTarget($target),
                ScopeMother::withSubject($reflector),
            ];
        }
    }
}
