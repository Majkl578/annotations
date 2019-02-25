<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_array;
use function is_object;
use function is_scalar;

/**
 * @internal
 */
final class MixedType implements Type
{
    public function describe() : string
    {
        return 'mixed';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return $value === null || is_scalar($value) || is_array($value) || is_object($value);
    }

    public function acceptsNull() : bool
    {
        return true;
    }
}
