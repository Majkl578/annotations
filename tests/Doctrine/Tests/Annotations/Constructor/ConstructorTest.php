<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Constructor;

use Doctrine\Annotations\Constructor\Constructor;
use Doctrine\Annotations\Constructor\Instantiator\ConstructorInstantiatorStrategy;
use Doctrine\Annotations\Constructor\Instantiator\Instantiator;
use Doctrine\Annotations\Constructor\Instantiator\PropertyInstantiatorStrategy;
use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Parser\Scope;
use Doctrine\Tests\Annotations\Fixtures\AnnotationWithConstructor;
use Doctrine\Tests\Annotations\Fixtures\Metadata\AnnotationWithConstructorMetadata;
use Doctrine\Tests\Annotations\Parser\ScopeMother;
use PHPUnit\Framework\TestCase;

final class ConstructorTest extends TestCase
{
    /** @var Constructor */
    private $constructor;

    public function setUp() : void
    {
        $this->constructor = new Constructor(
            new Instantiator(
                new ConstructorInstantiatorStrategy(),
                new PropertyInstantiatorStrategy()
            )
        );
    }

    /**
     * @param mixed[] $parameters
     *
     * @dataProvider validProvider
     */
    public function testCreatesAnnotation(
        AnnotationMetadata $annotationMetadata,
        Scope $scope,
        iterable $parameters,
        callable $asserter
    ) : void {
        $result = $this->constructor->construct($annotationMetadata, $scope, $parameters);

        $asserter($result);
    }

    /**
     * @return mixed[]
     */
    public function validProvider() : iterable
    {
        yield 'with constructor' => [
            AnnotationWithConstructorMetadata::get(),
            ScopeMother::example(),
            ['value' => 'foo'],
            static function (AnnotationWithConstructor $result) : void {
                self::assertSame('foo', $result->getValue());
            },
        ];
    }
}
