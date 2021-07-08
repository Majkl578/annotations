<?php

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationWithConstructor
{
    /**
     * @var string
     */
    private $value;

    public function __construct(array $values)
    {
        $this->value = $values['value'];
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
