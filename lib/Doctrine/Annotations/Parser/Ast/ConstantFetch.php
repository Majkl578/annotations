<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast;

use Doctrine\Annotations\Parser\Ast\Scalar\Identifier;
use Doctrine\Annotations\Parser\Visitor\Visitor;

final class ConstantFetch implements ValuableNode
{
    /** @var Identifier */
    private $name;

    public function __construct(Identifier $name)
    {
        $this->name = $name;
    }

    public function getName() : Identifier
    {
        return $this->name;
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitConstantFetch($this);
    }
}
