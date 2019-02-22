<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Reflection;

use ReflectionClass;

interface ClassReflectionProvider
{
    public function getClassReflection(string $name) : ReflectionClass;
}
