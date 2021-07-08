<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Assembler;

use Doctrine\Annotations\Annotation\Annotation as AnnotationAnnotation;
use Doctrine\Annotations\Annotation\Enum;
use Doctrine\Annotations\Annotation\Required as RequiredAnnotation;
use Doctrine\Annotations\Annotation\Target as TargetAnnotation;
use Doctrine\Annotations\Assembler\Assembler;
use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\InternalAnnotations;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use Doctrine\Annotations\Metadata\Reflection\ClassReflectionProvider;
use Doctrine\Annotations\Metadata\ScopeManufacturer;
use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\UnionType;
use Doctrine\Annotations\Parser\Ast\Annotations;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Compiler;
use Doctrine\Annotations\Parser\Reference\ReferenceResolver;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Annotations\TypeParser\TypeParser;
use ReflectionClass;
use ReflectionProperty;
use function assert;
use function is_array;
use function iterator_to_array;
use function stripos;

final class AnnotationMetadataAssembler
{
    /** @var Compiler */
    private $parser;

    /** @var ReferenceResolver */
    private $referenceResolver;

    /** @var ClassReflectionProvider */
    private $classReflectionProvider;

    /** @var TypeParser */
    private $typeParser;

    /** @var ScopeManufacturer */
    private $scopeManufacturer;

    /** @var Assembler */
    private $internalAssembler;

    public function __construct(
        Compiler $parser,
        ReferenceResolver $referenceResolver,
        ClassReflectionProvider $classReflectionProvider,
        TypeParser $typeParser,
        ScopeManufacturer $scopeManufacturer,
        Assembler $internalAssembler
    ) {
        $this->parser                  = $parser;
        $this->referenceResolver       = $referenceResolver;
        $this->classReflectionProvider = $classReflectionProvider;
        $this->typeParser              = $typeParser;
        $this->scopeManufacturer       = $scopeManufacturer;
        $this->internalAssembler       = $internalAssembler;
    }

    public function assemble(Reference $reference, Scope $scope) : AnnotationMetadata
    {
        $realName        = $this->referenceResolver->resolve($reference, $scope);
        $classReflection = $this->classReflectionProvider->getClassReflection($realName);
        $docComment      = $classReflection->getDocComment();
        $hasConstructor  = $classReflection->getConstructor() !== null;

        assert($docComment !== false, 'not an annotation');

        $annotations = $this->parser->compile($docComment);

        assert(stripos($docComment, '@') !== false);

        $hydratedAnnotations = $this->hydrateInternalAnnotations($annotations, $scope);

        assert($this->findAnnotation(AnnotationAnnotation::class, $hydratedAnnotations) !== null, 'not annotated with @Annotation');
        assert(! $hasConstructor || $classReflection->getConstructor()->isPublic(), 'constructor must be public');
        assert(! $hasConstructor || $classReflection->getConstructor()->getNumberOfParameters() === 1, 'constructor must accept a single parameter');

        return new AnnotationMetadata(
            $realName,
            $this->determineTarget($hydratedAnnotations),
            $hasConstructor,
            $this->assembleProperties($classReflection)
        );
    }

    /**
     * @param object[] $annotations
     */
    private function determineTarget(array $annotations) : AnnotationTarget
    {
        /** @var TargetAnnotation|null $target */
        $target = $this->findAnnotation(TargetAnnotation::class, $annotations);

        if ($target === null) {
            return new AnnotationTarget(AnnotationTarget::TARGET_ALL);
        }

        return new AnnotationTarget($target->targets);
    }

    /**
     * @return object[]
     */
    private function hydrateInternalAnnotations(Annotations $annotations, Scope $scope) : array
    {
        $assembled = $this->internalAssembler->collect(
            $annotations,
            new Scope($scope->getSubject(), InternalAnnotations::createImports(), clone $scope->getIgnoredAnnotations())
        );

        return is_array($assembled) ? $assembled : iterator_to_array($assembled);
    }

    /**
     * @param object[] $annotations
     */
    private function findAnnotation(string $name, array $annotations) : ?object
    {
        foreach ($annotations as $annotation) {
            if (! $annotation instanceof $name) {
                continue;
            }

            return $annotation;
        }

        return null;
    }

    /**
     * @return PropertyMetadata[]
     */
    private function assembleProperties(ReflectionClass $class) : array
    {
        $metadatas = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $i => $property) {
            $metadatas[] = $this->assembleProperty($property, $i === 0);
        }

        return $metadatas;
    }

    private function assembleProperty(ReflectionProperty $property, bool $first) : PropertyMetadata
    {
        $docBlock = $property->getDocComment();

        if ($docBlock === false) {
            return new PropertyMetadata(
                $property->getName(),
                new MixedType(),
                $first
            );
        }

        $scope               = $this->scopeManufacturer->manufacturePropertyScope($property);
        $hydratedAnnotations = $this->hydrateInternalAnnotations($this->parser->compile($docBlock), $scope);

        /** @var RequiredAnnotation|null $required */
        $required = $this->findAnnotation(RequiredAnnotation::class, $hydratedAnnotations);
        /** @var Enum|null $enum */
        $enum = $this->findAnnotation(Enum::class, $hydratedAnnotations);

        $type = $this->typeParser->parsePropertyType($property->getDocComment(), $scope);

        if ($required && ! $type->acceptsNull()) {
            $type = new UnionType($type, new NullType());
        }

        return new PropertyMetadata(
            $property->getName(),
            $type,
            $first,
            $enum ? $enum->value : [],
            $required !== null
        );
    }
}
