<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;
use Doctrine\Annotations\Type\FloatType;
use function sprintf;

/**
 * @internal
 */
final class ConstantFloatType extends FloatType implements ConstantScalarType
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

    public function describe() : string
    {
        return sprintf('%F', $this->value);
    }

    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
