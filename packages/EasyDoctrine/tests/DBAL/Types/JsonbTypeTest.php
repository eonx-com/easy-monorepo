<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\DBAL\Types\JsonbType;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\DBAL\Types\JsonbType
 */
final class JsonbTypeTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testConvertToDatabaseValueSucceeds
     */
    public function provideConvertToDatabaseValues(): array
    {
        return \array_merge(
            $this->provideConvertToPhpValues(),
            [
                'object phpValue' => [
                    'phpValue' => (object)[
                        'property' => 'value',
                    ],
                    'postgresValue' => '{"property":"value"}',
                ],
            ]
        );
    }

    /**
     * @return mixed[]
     *
     * @see testConvertToPhpValueSucceeds
     */
    public function provideConvertToPhpValues(): array
    {
        return [
            'null phpValue' => [
                'phpValue' => null,
                'postgresValue' => null,
            ],
            'empty phpValue' => [
                'phpValue' => [],
                'postgresValue' => '[]',
            ],
            'integer phpValue' => [
                'phpValue' => 13,
                'postgresValue' => '13',
            ],
            'float phpValue' => [
                'phpValue' => 13.93,
                'postgresValue' => '13.93',
            ],
            'string phpValue' => [
                'phpValue' => 'a string value',
                'postgresValue' => '"a string value"',
            ],
            'array of integers phpValue' => [
                'phpValue' => [681, 1185, 1878, 1989],
                'postgresValue' => '[681,1185,1878,1989]',
            ],
            'multidimensional array phpValue' => [
                'phpValue' => [
                    'key1' => 'value1',
                    'key2' => false,
                    'key3' => '15',
                    'key4' => 15,
                    'key5' => [112, 242, 309, 310],
                    'key6' => [
                        'sub-key-1' => 'foo',
                        'sub-key-2' => 'bar',
                        'sub-key-3' => 42,
                    ],
                ],
                'postgresValue' => '{"key3":"15","key1":"value1","key4":15,"key2":false,' .
                    '"key6":{"sub-key-2":"bar","sub-key-3":42,"sub-key-1":"foo"},"key5":[112,242,309,310]}',
            ],
        ];
    }

    /**
     * @param mixed $phpValue
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($phpValue, ?string $postgresValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $result = $type->convertToDatabaseValue($phpValue, $platform);

        self::assertSame($postgresValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = \urldecode('some incorrectly encoded utf string %C4');
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage(
            "Could not convert PHP type 'string' to 'json', as an " .
            "'Malformed UTF-8 characters, possibly incorrectly encoded' error was triggered by the serialization"
        );

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $phpValue
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideConvertToPhpValues
     */
    public function testConvertToPhpValueSucceeds($phpValue, ?string $postgresValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $result = $type->convertToPHPValue($postgresValue, $platform);

        self::assertSame($phpValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" to Doctrine Type JSONB');

        $type->convertToPHPValue($value, $platform);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);

        $name = $type->getName();

        self::assertSame(JsonbType::JSONB, $name);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetSQLDeclaration(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDoctrineTypeMapping($type::JSONB)->willReturn($type::JSONB);

        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platformReveal */
        $platformReveal = $platform->reveal();
        $result = $type->getSQLDeclaration([], $platformReveal);

        self::assertSame($type::JSONB, $result);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (Type::hasType(JsonbType::JSONB) === false) {
            Type::addType(JsonbType::JSONB, JsonbType::class);
        }
    }
}
