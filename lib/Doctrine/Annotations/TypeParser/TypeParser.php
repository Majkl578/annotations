<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Parser\Scope;

interface TypeParser
{
    public function parsePropertyType(string $docBlock, Scope $scope) : Type;
}
