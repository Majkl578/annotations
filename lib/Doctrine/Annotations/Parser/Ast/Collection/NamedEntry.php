<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast\Collection;

use Doctrine\Annotations\Parser\Ast\ClassConstantFetch;
use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Scalar\Identifier;
use Doctrine\Annotations\Parser\Ast\Scalar\IntegerScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\StringScalar;
use Doctrine\Annotations\Parser\Ast\ValuableNode;
use Doctrine\Annotations\Parser\Ast\Value;
use Doctrine\Annotations\Parser\Visitor\Visitor;
use function assert;

final class NamedEntry implements Entry
{
    /** @var StringScalar|IntegerScalar|ConstantFetch|ClassConstantFetch */
    private $key;

    /** @var ValuableNode */
    private $value;

    public function __construct(Value $key, ValuableNode $value)
    {
        assert(
            $key instanceof StringScalar
            || $key instanceof IntegerScalar
            || $key instanceof Identifier
            || $key instanceof ConstantFetch
            || $key instanceof ClassConstantFetch
        );

        $this->key   = $key;
        $this->value = $value;
    }

    /**
     * @return ClassConstantFetch|ConstantFetch|IntegerScalar|StringScalar|null
     */
    public function getKey() : ?Value
    {
        return $this->key;
    }

    public function getValue() : ValuableNode
    {
        return $this->value;
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitCollectionNamedEntry($this);
    }
}
