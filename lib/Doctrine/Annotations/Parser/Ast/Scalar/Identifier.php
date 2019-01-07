<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast\Scalar;

use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Scalar;
use Doctrine\Annotations\Parser\Visitor\Visitor;

final class Identifier implements Scalar
{
    /** @var string */
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getValue() : string
    {
        return $this->identifier;
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitIdentifier($this);
    }

    public function toConstantFetch() : ConstantFetch
    {
        return new ConstantFetch($this);
    }
}
