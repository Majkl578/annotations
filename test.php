<?php

use Doctrine\Annotations\TypeParser\NativeTypeParser;

require_once __DIR__ . '/vendor/autoload.php';

$parser = new NativeTypeParser();
$type   = $parser->parsePropertyType('@var array<int, array<int, stdClass[]>>[]', []);

var_dump($type);
var_dump($type->describe());
var_dump(
    $type->validate(
        [
            [
                1 => [
                    2 => [
                        new stdClass(),
                    ],
                ],
            ]
        ]
    )
);
