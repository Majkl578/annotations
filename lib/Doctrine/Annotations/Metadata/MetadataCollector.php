<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Assembler\Acceptor\ReferenceAcceptor;
use Doctrine\Annotations\Metadata\Assembler\AnnotationMetadataAssembler;
use Doctrine\Annotations\Parser\Ast\Annotation;
use Doctrine\Annotations\Parser\Ast\Annotations;
use Doctrine\Annotations\Parser\Ast\ClassConstantFetch;
use Doctrine\Annotations\Parser\Ast\Collection\Collection;
use Doctrine\Annotations\Parser\Ast\Collection\NamedEntry;
use Doctrine\Annotations\Parser\Ast\Collection\PositionalEntry;
use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Pair;
use Doctrine\Annotations\Parser\Ast\Parameter\NamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameter\UnnamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameters;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Ast\Scalar\BooleanScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\FloatScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\Identifier;
use Doctrine\Annotations\Parser\Ast\Scalar\IntegerScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\NullScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\StringScalar;
use Doctrine\Annotations\Parser\Reference\ReferenceResolver;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Annotations\Parser\Visitor\Visitor;
use SplObjectStorage;
use SplStack;

final class MetadataCollector
{
    /** @var AnnotationMetadataAssembler */
    private $assembler;

    /** @var ReferenceAcceptor */
    private $referenceAcceptor;

    /** @var ReferenceResolver */
    private $referenceResolver;

    public function __construct(
        AnnotationMetadataAssembler $assembler,
        ReferenceAcceptor $referenceAcceptor,
        ReferenceResolver $referenceResolver
    ) {
        $this->assembler         = $assembler;
        $this->referenceAcceptor = $referenceAcceptor;
        $this->referenceResolver = $referenceResolver;
    }

    public function collect(Annotations $node, Scope $scope, MetadataCollection $metadataCollection) : void
    {
        $storage = new SplObjectStorage();

        $node->dispatch($this->createInternalVisitor($storage, $scope));

        $this->filldMissingMetadata($metadataCollection, $scope, ...$storage);
    }

    private function filldMissingMetadata(
        MetadataCollection $metadataCollection,
        Scope $scope,
        Reference ...$references
    ) : void {
        foreach ($references as $reference) {
            $name = $this->referenceResolver->resolve($reference, $scope);

            if (isset($metadataCollection[$name])) {
                continue;
            }

            $metadataCollection[] = $this->assembler->assemble($reference, $scope);
        }
    }

    private function createInternalVisitor(SplObjectStorage $storage, Scope $scope) : Visitor
    {
        return new class ($this->assembler, $this->referenceAcceptor, $scope, $storage) implements Visitor
        {
            /** @var AnnotationMetadataAssembler */
            private $assembler;

            /** @var ReferenceAcceptor */
            private $referenceAcceptor;

            /** @var Scope */
            private $scope;

            /** @var SplObjectStorage<AnnotationMetadata> */
            private $storage;

            /** @var SplStack<mixed> */
            private $stack;

            public function __construct(
                AnnotationMetadataAssembler $assembler,
                ReferenceAcceptor $referenceAcceptor,
                Scope $scope,
                SplObjectStorage $storage
            ) {
                $this->assembler         = $assembler;
                $this->referenceAcceptor = $referenceAcceptor;
                $this->scope             = $scope;
                $this->storage           = $storage;
                $this->stack             = new SplStack();
            }

            public function visitAnnotations(Annotations $annotations) : void
            {
                foreach ($annotations as $annotation) {
                    $annotation->dispatch($this);
                }
            }

            public function visitAnnotation(Annotation $annotation) : void
            {
                if (! $this->referenceAcceptor->accepts($annotation->getName(), $this->scope)) {
                    return;
                }

                $annotation->getParameters()->dispatch($this);
                $annotation->getName()->dispatch($this);

                $this->storage->attach($this->stack->pop());
            }

            public function visitReference(Reference $reference) : void
            {
                $this->stack->push($reference);
            }

            public function visitParameters(Parameters $parameters) : void
            {
                foreach ($parameters as $parameter) {
                    $parameter->dispatch($this);
                }
            }

            public function visitNamedParameter(NamedParameter $parameter) : void
            {
                $parameter->getValue()->dispatch($this);
                $parameter->getName()->dispatch($this);
            }

            public function visitUnnamedParameter(UnnamedParameter $parameter) : void
            {
                $parameter->getValue()->dispatch($this);
            }

            public function visitCollection(Collection $collection) : void
            {
                foreach ($collection as $item) {
                    $item->dispatch($this);
                }
            }

            public function visitCollectionPositionalEntry(PositionalEntry $entry) : void
            {
                $entry->getValue()->dispatch($this);
            }

            public function visitCollectionNamedEntry(NamedEntry $entry) : void
            {
                $entry->getKey()->dispatch($this);
                $entry->getValue()->dispatch($this);
            }

            public function visitPair(Pair $pair) : void
            {
                $pair->getValue()->dispatch($this);
                $pair->getKey()->dispatch($this);
            }

            public function visitIdentifier(Identifier $identifier) : void
            {
                // leaf - no dispatch
            }

            public function visitConstantFetch(ConstantFetch $constantFetch) : void
            {
                $constantFetch->getName()->dispatch($this);
            }

            public function visitClassConstantFetch(ClassConstantFetch $classConstantFetch) : void
            {
                $classConstantFetch->getName()->dispatch($this);
                $classConstantFetch->getClass()->dispatch($this);
            }

            public function visitNullScalar(NullScalar $nullScalar) : void
            {
                // leaf - no dispatch
            }

            public function visitBooleanScalar(BooleanScalar $booleanScalar) : void
            {
                // leaf - no dispatch
            }

            public function visitIntegerScalar(IntegerScalar $integerScalar) : void
            {
                // leaf - no dispatch
            }

            public function visitFloatScalar(FloatScalar $floatScalar) : void
            {
                // leaf - no dispatch
            }

            public function visitStringScalar(StringScalar $stringScalar) : void
            {
                // leaf - no dispatch
            }
        };
    }
}
