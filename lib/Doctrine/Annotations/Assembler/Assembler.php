<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler;

use Doctrine\Annotations\Assembler\Acceptor\ReferenceAcceptor;
use Doctrine\Annotations\Assembler\Constant\ConstantResolver;
use Doctrine\Annotations\Constructor\Constructor;
use Doctrine\Annotations\Metadata\MetadataCollection;
use Doctrine\Annotations\Metadata\Reflection\ClassReflectionProvider;
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
use function array_key_exists;
use function assert;
use function strtolower;

final class Assembler
{
    /** @var MetadataCollection */
    private $metadataCollection;

    /** @var ReferenceResolver */
    private $referenceResolver;

    /** @var Constructor */
    private $constructor;

    /** @var ClassReflectionProvider */
    private $classReflectionProvider;

    /** @var ReferenceAcceptor */
    private $referenceAcceptor;

    /** @var ConstantResolver */
    private $constantResolver;

    public function __construct(
        MetadataCollection $metadataCollection,
        ReferenceResolver $referenceResolver,
        Constructor $constructor,
        ClassReflectionProvider $classReflectionProvider,
        ReferenceAcceptor $referenceAcceptor,
        ConstantResolver $constantResolver
    ) {
        $this->metadataCollection      = $metadataCollection;
        $this->referenceResolver       = $referenceResolver;
        $this->constructor             = $constructor;
        $this->classReflectionProvider = $classReflectionProvider;
        $this->referenceAcceptor       = $referenceAcceptor;
        $this->constantResolver        = $constantResolver;
    }

    /**
     * @return object[]
     */
    public function collect(Annotations $node, Scope $scope) : iterable
    {
        $storage = new SplObjectStorage();

        $node->dispatch($this->createInternalVisitor($storage, $scope));

        return yield from $storage;
    }

    private function createInternalVisitor(SplObjectStorage $storage, Scope $scope) : Visitor
    {
        return new class (
            $this->metadataCollection,
            $this->referenceResolver,
            $this->constructor,
            $this->classReflectionProvider,
            $this->referenceAcceptor,
            $this->constantResolver,
            $scope,
            $storage
        ) implements Visitor {
            /** @var MetadataCollection */
            private $metadataCollection;

            /** @var ReferenceResolver */
            private $referenceResolver;

            /** @var Constructor */
            private $constructor;

            /** @var ClassReflectionProvider */
            private $classReflectionProvider;

            /** @var ReferenceAcceptor */
            private $referenceAcceptor;

            /** @var ConstantResolver */
            private $constantResolver;

            /** @var Scope */
            private $scope;

            /** @var SplObjectStorage<object> */
            private $storage;

            /** @var SplStack<mixed> */
            private $stack;

            public function __construct(
                MetadataCollection $metadataCollection,
                ReferenceResolver $referenceResolver,
                Constructor $constructor,
                ClassReflectionProvider $classReflectionProvider,
                ReferenceAcceptor $referenceAcceptor,
                ConstantResolver $constantResolver,
                Scope $scope,
                SplObjectStorage $storage
            ) {
                $this->metadataCollection      = $metadataCollection;
                $this->referenceResolver       = $referenceResolver;
                $this->constructor             = $constructor;
                $this->classReflectionProvider = $classReflectionProvider;
                $this->referenceAcceptor       = $referenceAcceptor;
                $this->constantResolver        = $constantResolver;
                $this->scope                   = $scope;
                $this->storage                 = $storage;
                $this->stack                   = new SplStack();
            }

            public function visitAnnotations(Annotations $annotations) : void
            {
                foreach ($annotations as $annotation) {
                    $stackSize = $this->stack->count();

                    $annotation->dispatch($this);

                    if ($this->stack->count() === $stackSize) {
                        continue;
                    }

                    $this->storage->attach($this->stack->pop());
                }

                assert($this->stack->isEmpty());
            }

            public function visitAnnotation(Annotation $annotation) : void
            {
                if (! $this->referenceAcceptor->accepts($annotation->getName(), $this->scope)) {
                    return;
                }

                $this->scope->increaseNestingLevel();

                $annotation->getParameters()->dispatch($this);
                $annotation->getName()->dispatch($this);

                $this->stack->push(
                    $this->constructor->construct(
                        $this->metadataCollection[$this->stack->pop()],
                        $this->scope,
                        $this->stack->pop()
                    )
                );

                $this->scope->decreaseNestingLevel();
            }

            public function visitReference(Reference $reference) : void
            {
                $this->stack->push($this->referenceResolver->resolve($reference, $this->scope));
            }

            public function visitParameters(Parameters $parameters) : void
            {
                $new = [];

                foreach ($parameters as $parameter) {
                    $parameter->dispatch($this);

                    assert(! array_key_exists($this->stack->current(), $new));

                    $new[$this->stack->pop()] = $this->stack->pop();
                }

                $this->stack->push($new);
            }

            public function visitUnnamedParameter(UnnamedParameter $parameter) : void
            {
                $parameter->getValue()->dispatch($this);

                $this->stack->push($this->stack->pop());
                $this->stack->push(null);
            }

            public function visitNamedParameter(NamedParameter $parameter) : void
            {
                $parameter->getValue()->dispatch($this);
                $parameter->getName()->dispatch($this);
                // pass through
            }

            public function visitIdentifier(Identifier $identifier) : void
            {
                $this->stack->push($identifier->getValue());
            }

            public function visitPair(Pair $pair) : void
            {
                $pair->getValue()->dispatch($this);
                $pair->getKey()->dispatch($this);
                // pass through
            }

            public function visitBooleanScalar(BooleanScalar $booleanScalar) : void
            {
                $this->stack->push($booleanScalar->getValue());
            }

            public function visitIntegerScalar(IntegerScalar $integerScalar) : void
            {
                $this->stack->push($integerScalar->getValue());
            }

            public function visitFloatScalar(FloatScalar $floatScalar) : void
            {
                $this->stack->push($floatScalar->getValue());
            }

            public function visitStringScalar(StringScalar $stringScalar) : void
            {
                $this->stack->push($stringScalar->getValue());
            }

            public function visitNullScalar(NullScalar $nullScalar) : void
            {
                $this->stack->push($nullScalar->getValue());
            }

            public function visitCollection(Collection $collection) : void
            {
                $array = [];

                foreach ($collection as $item) {
                    $item->dispatch($this);

                    if ($item instanceof PositionalEntry) {
                        $array[] = $this->stack->pop();
                        continue;
                    }

                    assert($item instanceof NamedEntry);

                    [$value, $key] = [$this->stack->pop(), $this->stack->pop()];

                    $array[$key] = $value;
                }

                $this->stack->push($array);
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

            public function visitConstantFetch(ConstantFetch $constantFetch) : void
            {
                $constantFetch->getName()->dispatch($this);

                $constantName = $this->stack->pop();

                $this->stack->push($this->constantResolver->resolveStandaloneConstant($constantName));
            }

            public function visitClassConstantFetch(ClassConstantFetch $classConstantFetch) : void
            {
                $classConstantFetch->getName()->dispatch($this);
                $classConstantFetch->getClass()->dispatch($this);

                $className    = $this->stack->pop();
                $constantName = $this->stack->pop();

                if (strtolower($constantName) === 'class') {
                    $this->stack->push($className);
                    return;
                }

                $this->stack->push($this->constantResolver->resolveClassOrInterfaceConstant($className, $constantName));
            }
        };
    }
}
