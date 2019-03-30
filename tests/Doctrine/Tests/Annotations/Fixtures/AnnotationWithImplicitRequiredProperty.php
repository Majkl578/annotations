<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * @Annotation
 */
final class AnnotationWithImplicitRequiredProperty
{
    /** @var bool */
    public $a = false;

    /**
     * @Implicit
     * @Required
     * @var int
     */
    public $b = 0;

    public $c;
}
