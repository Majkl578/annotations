<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\MetadataCollection;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

final class MetadataCollectionTest extends TestCase
{
    public function testCollectionInterface() : void
    {
        $foo = $this->createDummyMetadata('Foo');
        $bar = $this->createDummyMetadata('Bar');
        $baz = $this->createDummyMetadata('Baz');

        $collection = new MetadataCollection();

        self::assertCount(0, $collection);
        self::assertSame([], iterator_to_array($collection));

        $collection->add($foo, $bar);

        self::assertCount(2, $collection);
        self::assertArrayHasKey('Foo', $collection);
        self::assertSame($foo, $collection['Foo']);
        self::assertArrayHasKey('Bar', $collection);
        self::assertSame($bar, $collection['Bar']);
        self::assertSame([$foo, $bar], iterator_to_array($collection));

        $collection[] = $baz;

        self::assertCount(3, $collection);
        self::assertArrayHasKey('Baz', $collection);
        self::assertSame($baz, $collection['Baz']);
        self::assertSame([$foo, $bar, $baz], iterator_to_array($collection));

        unset($collection['Bar']);

        self::assertCount(2, $collection);
        self::assertArrayNotHasKey('Bar', $collection);
        self::assertSame([$foo, $baz], iterator_to_array($collection));
    }

    public function testMetadataInConstructor() : void
    {
        self::assertCount(2, new MetadataCollection($this->createDummyMetadata('A'), $this->createDummyMetadata('B')));
    }

    private function createDummyMetadata(string $name) : AnnotationMetadata
    {
        return new AnnotationMetadata($name, AnnotationTarget::all(), false);
    }
}
