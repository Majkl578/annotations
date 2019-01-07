<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast\Collection;

use Doctrine\Annotations\Parser\Ast\ValuableNode;
use Doctrine\Annotations\Parser\Visitor\Visitor;

final class PositionalEntry implements Entry
{
    /** @var ValuableNode */
    private $value;

    public function __construct(ValuableNode $value)
    {
        $this->value = $value;
    }

    public function getValue() : ValuableNode
    {
        return $this->value;
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitCollectionPositionalEntry($this);
    }
}
