<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use function array_key_exists;
use function array_values;
use function assert;
use function count;
use function sprintf;

/**
 * @internal
 */
final class MetadataCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<string, AnnotationMetadata> */
    private $metadata = [];

    public function __construct(AnnotationMetadata ...$metadatas)
    {
        $this->add(...$metadatas);
    }

    public function add(AnnotationMetadata ...$metadatas) : void
    {
        foreach ($metadatas as $metadata) {
            assert(! isset($this[$metadata->getName()]), sprintf('Metadata with name %s already exists.', $metadata->getName()));

            $this->metadata[$metadata->getName()] = $metadata;
        }
    }

    public function include(self $other) : void
    {
        $this->add(...$other);
    }

    /**
     * @param string $name
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function offsetGet($name) : AnnotationMetadata
    {
        assert(isset($this[$name]), sprintf('Metadata for name %s does not exist', $name));

        return $this->metadata[$name];
    }

    /**
     * @param null               $name
     * @param AnnotationMetadata $metadata
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function offsetSet($name, $metadata) : void
    {
        assert($name === null, 'Setting named metadata is not supported.');

        $this->add($metadata);
    }

    /**
     * @param string $name
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function offsetExists($name) : bool
    {
        return array_key_exists($name, $this->metadata);
    }

    /**
     * @param string $name
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function offsetUnset($name) : void
    {
        assert(isset($this[$name]));

        unset($this->metadata[$name]);
    }

    public function count() : int
    {
        return count($this->metadata);
    }

    /**
     * @return Traversable<AnnotationMetadata>
     */
    public function getIterator() : Traversable
    {
        yield from array_values($this->metadata);
    }
}
