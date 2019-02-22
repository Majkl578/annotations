<?php

namespace Doctrine\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\PropertyMetadata;

final class PropertyMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var string[]|null */
    private $type;

    /** @var bool */
    private $required = false;

    /** @var bool */
    private $default = false;

    /** @var mixed[]|null */
    private $enum;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string[] $type
     */
    public function withType(array $type) : self
    {
        $new       = clone $this;
        $new->type = $type;

        return $new;
    }

    public function withBeingRequired() : self
    {
        $new           = clone $this;
        $new->required = true;

        return $new;
    }

    public function withBeingDefault() : self
    {
        $new          = clone $this;
        $new->default = true;

        return $new;
    }

    /**
     * @param mixed[] $values
     */
    public function withEnum(array $enum) : self
    {
        $new       = clone $this;
        $new->enum = $enum;

        return $new;
    }

    public function build() : PropertyMetadata
    {
        return new PropertyMetadata(
            $this->name,
            $this->type,
            $this->required,
            $this->default,
            $this->enum
        );
    }
}
