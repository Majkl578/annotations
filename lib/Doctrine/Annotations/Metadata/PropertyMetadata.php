<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

final class PropertyMetadata
{
    /** @var string */
    private $name;

    /** @var array<string, string>|null */
    private $type;

    /** @var bool */
    private $required;

    /** @var bool */
    private $default;

    /** @var mixed[]|null */
    private $enum;

    /**
     * @param array<string, string> $type
     * @param mixed[]|null          $enum
     */
    public function __construct(
        string $name,
        ?array $type,
        bool $required = false,
        bool $default = false,
        ?array $enum = null
    ) {
        $this->name     = $name;
        $this->type     = $type;
        $this->required = $required;
        $this->default  = $default;
        $this->enum     = $enum;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * @return array<string, string>|null
     */
    public function getType() : ?array
    {
        return $this->type;
    }

    public function isDefault() : bool
    {
        return $this->default;
    }

    /**
     * @return mixed[]|null
     */
    public function getEnum() : ?array
    {
        return $this->enum;
    }
}
