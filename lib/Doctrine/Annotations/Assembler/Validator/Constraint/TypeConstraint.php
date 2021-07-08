<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Assembler\Validator\Constraint;

use Doctrine\Annotations\Assembler\Validator\Constraint\Exception\InvalidType;
use Doctrine\Annotations\Metadata\Type\Type;

final class TypeConstraint implements Constraint
{
    /** @var Type */
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : void
    {
        if (! $this->type->validate($value)) {
            throw InvalidType::new($this->type->describe(), $value);
        }
    }
}
