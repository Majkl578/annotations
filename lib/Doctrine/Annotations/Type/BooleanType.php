<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_bool;

/**
 * @internal
 */
class BooleanType implements ScalarType
{
    public function describe() : string
    {
        return 'boolean';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_bool($value);
    }

    public function acceptsNull() : bool
    {
        return false;
    }
}
