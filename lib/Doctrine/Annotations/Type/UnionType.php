<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function array_map;
use function assert;
use function count;
use function implode;
use function sprintf;

/**
 * @internal
 */
final class UnionType implements CompositeType
{
    /** @var Type[] */
    private $subTypes;

    public function __construct(Type ...$subTypes)
    {
        assert(count($subTypes) >= 2);

        $this->subTypes = $subTypes;
    }

    public function describe() : string
    {
        return implode(
            '|',
            array_map(
                static function (Type $subType) : string {
                    if ($subType instanceof CompositeType) {
                        return sprintf('(%s)', $subType->describe());
                    }

                    return $subType->describe();
                },
                $this->subTypes
            )
        );
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        foreach ($this->subTypes as $subType) {
            if (! $subType->validate($value)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function acceptsNull() : bool
    {
        foreach ($this->subTypes as $subType) {
            if (! $subType->acceptsNull()) {
                continue;
            }

            return true;
        }

        return false;
    }
}
