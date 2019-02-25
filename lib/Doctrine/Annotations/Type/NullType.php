<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
final class NullType implements ScalarType
{
    public function describe() : string
    {
        return 'null';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return $value === null;
    }

    public function acceptsNull() : bool
    {
        return true;
    }
}
