<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser\Exception;

use LogicException;

final class TypeNotSupported extends LogicException
{
    public static function fromInvalidType(string $name) : self
    {
        return new self('Type "%s" is not supported by Doctrine Annotations.', $name);
    }

    public static function callableNotSupported() : self
    {
        return self::fromInvalidType('callable');
    }
}
