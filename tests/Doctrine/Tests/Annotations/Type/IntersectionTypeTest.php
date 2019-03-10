<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Countable;
use Doctrine\Annotations\Type\IntersectionType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Annotations\Type\UnionType;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use stdClass;
use Traversable;

final class IntersectionTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new IntersectionType(
            new ObjectType(Countable::class),
            new ObjectType(JsonSerializable::class),
            new UnionType(
                new ObjectType(Iterator::class),
                new ObjectType(IteratorAggregate::class)
            )
        );
    }

    public function getDescription() : string
    {
        return 'Countable&JsonSerializable&(Iterator|IteratorAggregate)';
    }

    /**
     * @return object[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [
            new class implements Countable, IteratorAggregate, JsonSerializable {
                /**
                 * @return int[]
                 */
                public function getIterator() : Traversable
                {
                    yield 123;
                }

                public function count() : int
                {
                    return 1;
                }

                /**
                 * @return int[]
                 */
                public function jsonSerialize() : array
                {
                    return [];
                }
            },
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [true];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new stdClass()];
        yield [
            new class implements Countable {
                public function count() : int
                {
                    return 1;
                }
            },
        ];
        yield [
            new class implements JsonSerializable {
                /**
                 * @return int[]
                 */
                public function jsonSerialize() : array
                {
                    return [123];
                }
            },
        ];
        yield [
            new class implements IteratorAggregate {
                /**
                 * @return int[]
                 */
                public function getIterator() : Traversable
                {
                    yield 123;
                }
            },
        ];
    }
}
