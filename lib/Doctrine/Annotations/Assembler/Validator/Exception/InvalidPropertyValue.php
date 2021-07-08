<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Exception;

use Doctrine\Annotations\Metadata\PropertyMetadata;
use Exception;
use Throwable;
use function sprintf;

final class InvalidPropertyValue extends Exception implements ValidationException
{
    private function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function new(PropertyMetadata $propertyMetadata, Throwable $previous) : self
    {
        return new self(
            sprintf(
                'Invalid value for property %s',
                $propertyMetadata->getName()
            ),
            $previous
        );
    }
}
