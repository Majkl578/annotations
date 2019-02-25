<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;
use Doctrine\Annotations\Type\FloatType as GenericFloatType;

/**
 * @internal
 */
final class FloatType extends GenericFloatType implements ConstantScalarType
{
    /** @var float */
    private $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue() : float
    {
        return $this->value;
    }

    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
