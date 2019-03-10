<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\ListType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\Type;
use stdClass;
use function sprintf;

final class ListTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new ListType($this->getInternalType());
    }

    public function getDescription() : string
    {
        return sprintf('array<%s>', $this->getInternalType()->describe());
    }

    /**
     * @return mixed[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [
            ['foo', 'bar'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [
            ['foo', 1],
            ['foo' => 'bar'],
            [new stdClass()],
        ];
    }

    private function getInternalType() : Type
    {
        return new StringType();
    }
}
