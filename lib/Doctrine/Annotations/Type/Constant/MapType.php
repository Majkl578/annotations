<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;
use Doctrine\Annotations\Type\ConstantType;
use Doctrine\Annotations\Type\MapType as GenericMapType;
use function assert;

/**
 * @internal
 */
final class MapType extends GenericMapType implements ConstantType
{
    /** @var array<int|string, mixed> */
    private $value;

    /**
     * @param array<int|string, mixed> $value
     */
    public function __construct(ConstantScalarType $keyType, ConstantType $valueType, $value)
    {
        assert($keyType instanceof IntegerType || $keyType instanceof StringType, 'Invalid key type.');

        parent::__construct($keyType, $valueType);

        $this->value = $value;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getValue() : array
    {
        return $this->value;
    }

    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
