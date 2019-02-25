<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\TypeParser;

use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Parser\IgnoredAnnotations;
use Doctrine\Annotations\Parser\Imports;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Annotations\TypeParser\TypeParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

abstract class TypeParserTest extends TestCase
{
    protected const MOCK_IMPORTS = ['foobar' => 'FooBaz'];

    /** @var TypeParser */
    private $parser;

    final protected function setUp() : void
    {
        parent::setUp();

        $this->parser = $this->createParser();
    }

    /**
     * @dataProvider validPropertyTypesProvider()
     */
    final public function testValidPropertyTypes(string $phpDoc, Type $expected) : void
    {
        $actual = $this->parser->parsePropertyType(
            $phpDoc,
            new Scope(
                new ReflectionClass(stdClass::class),
                new Imports(self::MOCK_IMPORTS),
                new IgnoredAnnotations()
            )
        );

        self::assertSame($expected->describe(), $actual->describe());
    }

    abstract protected function createParser() : TypeParser;

    /**
     * @return string[][]|Type[][]
     */
    abstract protected function validPropertyTypesProvider() : iterable;
}
