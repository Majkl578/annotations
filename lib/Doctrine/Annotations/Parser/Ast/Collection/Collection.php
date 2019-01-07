<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Ast\Collection;

use Countable;
use Doctrine\Annotations\Parser\Ast\Value;
use Doctrine\Annotations\Parser\Visitor\Visitor;
use IteratorAggregate;
use function count;

final class Collection implements Value, IteratorAggregate, Countable
{
    /** @var Entry[] */
    private $items;

    public function __construct(Entry ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Entry[]
     */
    public function getIterator() : iterable
    {
        yield from $this->items;
    }

    public function count() : int
    {
        return count($this->items);
    }

    public function dispatch(Visitor $visitor) : void
    {
        $visitor->visitCollection($this);
    }
}
