<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Visitor;

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

interface Visitor
{
    public function visitAnnotations(Annotations $annotations) : void;

    public function visitAnnotation(Annotation $annotation) : void;

    public function visitReference(Reference $reference) : void;

    public function visitParameters(Parameters $parameters) : void;

    public function visitNamedParameter(NamedParameter $parameter) : void;

    public function visitUnnamedParameter(UnnamedParameter $parameter) : void;

    public function visitCollection(Collection $collection) : void;

    public function visitCollectionPositionalEntry(PositionalEntry $entry) : void;

    public function visitCollectionNamedEntry(NamedEntry $entry) : void;

    public function visitPair(Pair $pair) : void;

    public function visitIdentifier(Identifier $identifier) : void;

    public function visitConstantFetch(ConstantFetch $constantFetch) : void;

    public function visitClassConstantFetch(ClassConstantFetch $classConstantFetch) : void;

    public function visitNullScalar(NullScalar $nullScalar) : void;

    public function visitBooleanScalar(BooleanScalar $booleanScalar) : void;

    public function visitIntegerScalar(IntegerScalar $integerScalar) : void;

    public function visitFloatScalar(FloatScalar $floatScalar) : void;

    public function visitStringScalar(StringScalar $stringScalar) : void;
}
