<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantType;
use Doctrine\Annotations\Type\ListType as GenericListType;

/**
 * @internal
 */
final class ListType extends GenericListType implements ConstantType
{
    /** @var array<mixed> */
    private $value;

    /**
     * @param array<mixed> $value
     */
    public function __construct(ConstantType $valueType, $value)
    {
        parent::__construct($valueType);

        $this->value = $value;
    }

    /**
     * @return array<mixed>
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
