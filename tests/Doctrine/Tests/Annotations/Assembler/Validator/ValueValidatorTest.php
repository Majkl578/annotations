<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Assembler\Validator;

use Doctrine\Annotations\Assembler\Validator\Exception\InvalidPropertyValue;
use Doctrine\Annotations\Assembler\Validator\ValueValidator;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Tests\Annotations\Metadata\Type\PropertyMetadataMother;
use PHPUnit\Framework\TestCase;

final class ValueValidatorTest extends TestCase
{
    /** @var ValueValidator */
    private $validator;

    protected function setUp() : void
    {
        $this->validator = new ValueValidator();
    }

    /**
     * @param mixed $value
     *
     * @dataProvider validTypeValidationProvider
     */
    public function testValidatesValuesMatchingType(Type $type, $value) : void
    {
        $metadata = PropertyMetadataMother::withType($type);

        $this->validator->validate($metadata, $value);

        self::assertTrue(true);
    }

    /**
     * @return mixed[]
     */
    public function validTypeValidationProvider() : iterable
    {
        yield 'valid string' => [
            new StringType(),
            'foo',
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider invalidTypeValidationProvider
     */
    public function testNotValidatesValuesNotMatchingTypeAndThrows(Type $type, $value) : void
    {
        $metadata = PropertyMetadataMother::withType($type);

        $this->expectException(InvalidPropertyValue::class);

        $this->validator->validate($metadata, $value);
    }

    /**
     * @return mixed[]
     */
    public function invalidTypeValidationProvider() : iterable
    {
        yield 'value not matching property type' => [
            new StringType(),
            42,
        ];
    }
}
