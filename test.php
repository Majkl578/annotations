<?php

require_once __DIR__ . '/vendor/autoload.php';

$compiler = Hoa\Compiler\Llk\Llk::load(new Hoa\File\Read(__DIR__ . '/lib/Doctrine/Annotations/TypeParser/type.pp'));


$ast = $compiler->parse('int&int&int|int', 'any');

echo (new Hoa\Compiler\Visitor\Dump())->visit($ast);
