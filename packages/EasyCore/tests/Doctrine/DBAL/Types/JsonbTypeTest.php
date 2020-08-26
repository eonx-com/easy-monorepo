<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyCore\Doctrine\DBAL\Types\JsonbType;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType
 */
final class JsonbTypeTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testConvertToDatabaseValueSucceeds
     * @see testConvertToPhpValueSucceeds
     */
    public function provideConvertToDatabaseValues(): array
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
            'array og integers phpValue' => [
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
                ],
                'postgresValue' => '{"key1":"value1","key2":false,"key3":"15","key4":15,"key5":[112,242,309,310]}',
            ],
        ];
    }

    /**
     * @param array|float|int|string|null $phpValue
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($phpValue, ?string $postgresValue): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $result = $type->convertToDatabaseValue($phpValue, $platform);

        self::assertSame($postgresValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = \urldecode('some bad utf string %C4');
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP type 'string' to 'json', as an " .
            "'Malformed UTF-8 characters, possibly incorrectly encoded' error was triggered by the serialization"
        );

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param array|float|int|string|null $phpValue
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToPhpValueSucceeds($phpValue, ?string $postgresValue): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $result = $type->convertToPHPValue($postgresValue, $platform);

        self::assertSame($phpValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type jsonb');

        $type->convertToPHPValue($value, $platform);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        $name = $type->getName();

        self::assertSame(JsonbType::TYPE_NAME, $name);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetSQLDeclaration(): void
    {
        /** @var \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType $type */
        $type = Type::getType(JsonbType::TYPE_NAME);
        $platform = $this->prophesize(AbstractPlatform::class);

        $platform->getDoctrineTypeMapping($type::TYPE_NAME)
            ->shouldBeCalled()
            ->withArguments([$type::TYPE_NAME])
            ->willReturn($type::TYPE_NAME);

        self::assertSame(
            $type::TYPE_NAME,
            $type->getSQLDeclaration([], $platform->reveal())
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (Type::hasType(JsonbType::TYPE_NAME) === false) {
            Type::addType(JsonbType::TYPE_NAME, JsonbType::class);
        }
    }
}
