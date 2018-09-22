<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast;

use Doctrine\Annotations\Parser\Visitor\Visitor;
use IteratorAggregate;

final class Parameters implements Node, IteratorAggregate
{
    /** @var Parameter[] */
    private $parameters;

    public function __construct(Parameter ...$parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return Parameter[]
     */
    public function getIterator() : iterable
    {
        yield from $this->parameters;
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitParameters($this);
    }
}