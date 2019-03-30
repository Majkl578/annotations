<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * @Annotation
 */
final class AnnotationWithImplicitProperty
{
    /** @var bool */
    public $a = false;

    /**
     * @Implicit
     * @var int
     */
    public $b = 0;

    public $c;
}
