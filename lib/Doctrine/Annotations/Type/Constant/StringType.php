<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;
use Doctrine\Annotations\Type\StringType as GenericStringType;

/**
 * @internal
 */
final class StringType extends GenericStringType implements ConstantScalarType
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
