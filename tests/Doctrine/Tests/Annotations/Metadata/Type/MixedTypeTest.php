<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Type;

use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\Type;
use stdClass;
use function fclose;
use function fopen;

final class MixedTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new MixedType();
    }

    public function testDescribe() : void
    {
        self::assertSame('mixed', $this->getType()->describe());
    }

    /**
     * @return mixed[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [null];
        yield [true];
        yield [123];
        yield [1.234];
        yield ['hello'];
        yield [[123]];
        yield [new stdClass()];
    }

    /**
     * @return resource[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        try {
            yield [$f = fopen(__FILE__, 'r')];
        } finally {
            @fclose($f);
        }
    }

    public function testAcceptsNull() : void
    {
        self::assertTrue($this->getType()->acceptsNull());
    }
}
