<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Visitor;

use Doctrine\Annotations\Parser\Ast\Annotation;
use Doctrine\Annotations\Parser\Ast\Annotations;
use Doctrine\Annotations\Parser\Ast\ClassConstantFetch;
use Doctrine\Annotations\Parser\Ast\Collection\Collection;
use Doctrine\Annotations\Parser\Ast\Collection\Entry;
use Doctrine\Annotations\Parser\Ast\Collection\NamedEntry;
use Doctrine\Annotations\Parser\Ast\Collection\PositionalEntry;
use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Node;
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
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_UNESCAPED_UNICODE;
use function count;
use function json_encode;
use function str_repeat;
use function strlen;
use function substr;

final class Dumper implements Visitor
{
    private const INDENT = '>  ';

    /** @var int */
    private $depth = 0;

    public function visit(Node $node) : void
    {
        $node->dispatch($this);
    }

    public function visitAnnotations(Annotations $annotations) : void
    {
        $this->print(Annotations::class);

        $this->depth++;
        foreach ($annotations as $annotation) {
            $annotation->dispatch($this);
        }
        $this->depth--;
    }

    public function visitAnnotation(Annotation $annotation) : void
    {
        $this->print(Annotation::class);

        $this->depth++;
        $annotation->getName()->dispatch($this);
        $annotation->getParameters()->dispatch($this);
        $this->depth--;
    }

    public function visitReference(Reference $reference) : void
    {
        $this->print(
            Reference::class,
            ['identifier' => $reference->getIdentifier(), 'fully_qualified' => $reference->isFullyQualified()]
        );
    }

    public function visitParameters(Parameters $parameters) : void
    {
        $this->print(Parameters::class);

        $this->depth++;
        foreach ($parameters as $parameter) {
            $parameter->dispatch($this);
        }
        $this->depth--;
    }

    public function visitNamedParameter(NamedParameter $parameter) : void
    {
        $this->print(NamedParameter::class);

        $this->depth++;
        $parameter->getName()->dispatch($this);
        $parameter->getValue()->dispatch($this);
        $this->depth--;
    }

    public function visitUnnamedParameter(UnnamedParameter $parameter) : void
    {
        $this->print(UnnamedParameter::class);

        $this->depth++;
        $parameter->getValue()->dispatch($this);
        $this->depth--;
    }

    public function visitCollection(Collection $collection) : void
    {
        $this->print(Collection::class);

        $this->depth++;
        foreach ($collection as $item) {
            $item->dispatch($this);
        }
        $this->depth--;
    }

    public function visitCollectionPositionalEntry(PositionalEntry $entry) : void
    {
        $this->print(Entry::class);

        $this->depth++;
        $entry->getValue()->dispatch($this);
        $this->depth--;
    }

    public function visitCollectionNamedEntry(NamedEntry $entry) : void
    {
        $this->print(Entry::class);

        $this->depth++;
        $entry->getKey()->dispatch($this);
        $entry->getValue()->dispatch($this);
        $this->depth--;
    }

    public function visitPair(Pair $pair) : void
    {
        $this->print(Pair::class);

        $this->depth++;
        $pair->getKey()->dispatch($this);
        $pair->getValue()->dispatch($this);
        $this->depth--;
    }

    public function visitIdentifier(Identifier $identifier) : void
    {
        $this->print(Identifier::class, ['value' => $identifier->getValue()]);
    }

    public function visitConstantFetch(ConstantFetch $constantFetch) : void
    {
        $this->print(ConstantFetch::class);

        $this->depth++;
        $constantFetch->getName()->dispatch($this);
        $this->depth--;
    }

    public function visitClassConstantFetch(ClassConstantFetch $classConstantFetch) : void
    {
        $this->print(ClassConstantFetch::class);

        $this->depth++;
        $classConstantFetch->getClass()->dispatch($this);
        $classConstantFetch->getName()->dispatch($this);
        $this->depth--;
    }

    public function visitNullScalar(NullScalar $nullScalar) : void
    {
        $this->print(NullScalar::class, ['value' => $nullScalar->getValue()]);
    }

    public function visitBooleanScalar(BooleanScalar $booleanScalar) : void
    {
        $this->print(BooleanScalar::class, ['value' => $booleanScalar->getValue()]);
    }

    public function visitIntegerScalar(IntegerScalar $integerScalar) : void
    {
        $this->print(IntegerScalar::class, ['value' => $integerScalar->getValue()]);
    }

    public function visitFloatScalar(FloatScalar $floatScalar) : void
    {
        $this->print(FloatScalar::class, ['value' => $floatScalar->getValue()]);
    }

    public function visitStringScalar(StringScalar $stringScalar) : void
    {
        $this->print(StringScalar::class, ['value' => $stringScalar->getValue()]);
    }

    /**
     * @param mixed[] $data
     */
    private function print(string $name, array $data = []) : void
    {
        echo str_repeat(self::INDENT, $this->depth + 1);
        echo substr($name, strlen(Node::class) - 4);

        if (count($data) !== 0) {
            echo ' ', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }

        echo "\n";
    }
}
