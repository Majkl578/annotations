<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

/**
 * @internal
 */
final class Nodes
{
    public const ARRAY        = '#array';
    public const BOOLEAN      = '#boolean';
    public const CALLABLE     = '#callable';
    public const FLOAT        = '#float';
    public const GENERIC      = '#generic';
    public const INTEGER      = '#integer';
    public const ITERABLE     = '#iterable';
    public const LIST         = '#list';
    public const NULL         = '#null';
    public const OBJECT       = '#object';
    public const STRING       = '#string';
    public const UNION        = '#union';
    public const INTERSECTION = '#intersection';

    private function __construct()
    {
    }
}
