<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\PropertyMetadata;

final class AnnotationMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var bool */
    private $hasConstructor;

    /** @var PropertyMetadata[] */
    private $properties;

    public function __construct()
    {
        $this->name           = 'foo';
        $this->target         = new AnnotationTarget(AnnotationTarget::TARGET_ALL);
        $this->hasConstructor = false;
        $this->properties     = [];
    }

    public function withTarget(AnnotationTarget $target) : self
    {
        $this->target = $target;

        return $this;
    }

    public function hasConstructor() : self
    {
        $this->hasConstructor = true;

        return $this;
    }

    public function withProperties(PropertyMetadata ...$properties) : self
    {
        $this->properties = $properties;

        return $this;
    }

    public function build() : AnnotationMetadata
    {
        return new AnnotationMetadata(
            $this->name,
            $this->target,
            $this->hasConstructor,
            $this->properties
        );
    }
}
