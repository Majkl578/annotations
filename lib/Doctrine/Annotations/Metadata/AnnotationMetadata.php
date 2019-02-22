<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use function array_combine;
use function array_filter;
use function array_map;
use function array_values;
use function assert;
use function count;

/**
 * @internal
 */
final class AnnotationMetadata
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var bool */
    private $usesConstructor;

    /** @var PropertyMetadata[] */
    private $properties;

    /** @var PropertyMetadata|null */
    private $defaultProperty;

    /**
     * @param PropertyMetadata[] $properties
     */
    public function __construct(
        string $name,
        AnnotationTarget $target,
        bool $hasConstructor,
        PropertyMetadata ...$properties
    ) {
        $this->name           = $name;
        $this->target         = $target;
        $this->usesConstructor = $hasConstructor;
        $this->properties     = array_combine(
            array_map(
                static function (PropertyMetadata $property) : string {
                    return $property->getName();
                },
                $properties
            ),
            $properties
        );

        $defaultProperties = array_values(
            array_filter(
                $properties,
                static function (PropertyMetadata $property) : bool {
                    return $property->isDefault();
                }
            )
        );

        assert(count($defaultProperties) <= 1);

        $this->defaultProperty = $defaultProperties[0] ?? null;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTarget() : AnnotationTarget
    {
        return $this->target;
    }

    public function usesConstructor() : bool
    {
        return $this->usesConstructor;
    }

    /**
     * @return PropertyMetadata[]
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getDefaultProperty() : ?PropertyMetadata
    {
        return $this->defaultProperty;
    }
}
