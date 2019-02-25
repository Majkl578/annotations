<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function assert;
use function is_array;
use function sprintf;

/**
 * @internal
 */
class MapType implements Type
{
    /** @var ScalarType|UnionType */
    private $keyType;

    /** @var Type */
    private $valueType;

    public function __construct(Type $keyType, Type $valueType)
    {
        assert($keyType instanceof ScalarType || $keyType instanceof UnionType, 'Invalid key type');

        $this->keyType   = $keyType;
        $this->valueType = $valueType;
    }

    public function getKeyType() : ScalarType
    {
        return $this->keyType;
    }

    public function getValueType() : Type
    {
        return $this->valueType;
    }

    public function describe() : string
    {
        return sprintf('array<%s, %s>', $this->keyType->describe(), $this->valueType->describe());
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $innerValue) {
            if (! $this->keyType->validate($key)) {
                return false;
            }

            if (! $this->valueType->validate($innerValue)) {
                return false;
            }
        }

        return true;
    }

    public function acceptsNull() : bool
    {
        return $this->valueType->acceptsNull();
    }
}
