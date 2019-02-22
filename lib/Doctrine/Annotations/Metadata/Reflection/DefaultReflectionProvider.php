<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Reflection;

use ReflectionClass;

final class DefaultReflectionProvider implements ClassReflectionProvider
{
    public function getClassReflection(string $name) : ReflectionClass
    {
        // TODO: catch exception
        return new ReflectionClass($name);
    }
}
