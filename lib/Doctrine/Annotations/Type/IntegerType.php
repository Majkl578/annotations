<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_int;

/**
 * @internal
 */
class IntegerType implements ScalarType
{
    public function describe() : string
    {
        return 'integer';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_int($value);
    }

    public function acceptsNull() : bool
    {
        return false;
    }
}
