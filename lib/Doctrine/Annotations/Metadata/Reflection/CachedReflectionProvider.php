<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Reflection;

use ReflectionClass;

final class CachedReflectionProvider implements ClassReflectionProvider
{
    /** @var ClassReflectionProvider */
    private $innerClassReflectionProvider;

    /** @var ReflectionClass[] */
    private $classReflections = [];

    public function __construct(ClassReflectionProvider $innerClassReflectionProvider)
    {
        $this->innerClassReflectionProvider = $innerClassReflectionProvider;
    }

    public function getClassReflection(string $name) : ReflectionClass
    {
        return $this->classReflections[$name]
            ?? $this->classReflections[$name] = $this->innerClassReflectionProvider->getClassReflection($name);
    }
}
