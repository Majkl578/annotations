<?php

namespace Doctrine\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\PropertyMetadata;

final class AnnotationMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var PropertyMetadata[] */
    private $properties = [];

    /** @var bool */
    private $constructor = false;

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

    public function withConstructor() : self
    {
        $new              = clone $this;
        $new->constructor = true;

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
            $this->constructor,
            ...$this->properties
        );
    }
}
