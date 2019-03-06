<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

use Doctrine\Annotations\Type\Type;

interface TypeParser
{
    /**
     * @param array<string, string> $imports
     */
    public function parsePropertyType(string $docBlock, array $imports) : Type;
}
