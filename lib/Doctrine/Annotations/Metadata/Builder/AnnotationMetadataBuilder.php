<?php

namespace Doctrine\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\PropertyMetadata;

/**
 * @internal
 */
final class AnnotationMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var PropertyMetadata[] */
    private $properties = [];

    /** @var bool */
    private $usesConstructor = false;

    public function __construct(string $name)
    {
        $this->name   = $name;
        $this->target = AnnotationTarget::all();
    }

    public function withTarget(AnnotationTarget $target) : self
    {
        $new         = clone $this;
        $new->target = $target;

        return $new;
    }

    public function withUsingConstructor() : self
    {
        $new                  = clone $this;
        $new->usesConstructor = true;

        return $new;
    }

    public function withProperty(PropertyMetadata $property) : self
    {
        $new               = clone $this;
        $new->properties[] = $property;

        return $new;
    }

    public function build() : AnnotationMetadata
    {
        return new AnnotationMetadata(
            $this->name,
            $this->target,
            $this->usesConstructor,
            ...$this->properties
        );
    }
}
