<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser;

final class Nodes
{
    public const ANNOTATION          = '#annotation';
    public const ANNOTATIONS         = '#annotations';
    public const CLASS_CONSTANT      = '#class_constant';
    public const COLLECTION          = '#collection';
    public const COLLECTION_ENTRY    = '#collection_entry';
    public const NAMED_PARAMETERS    = '#named_parameter';
    public const PAIR                = '#pair';
    public const PARAMETERS          = '#parameters';
    public const REFERENCE           = '#reference';
    public const STANDALONE_CONSTANT = '#standalone_constant';
    public const STRING              = '#string';
    public const UNNAMED_PARAMETERS  = '#unnamed_parameter';
    public const VALUE               = '#value';

    private function __construct()
    {
    }
}
