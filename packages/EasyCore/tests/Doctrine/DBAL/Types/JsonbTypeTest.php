<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyCore\Doctrine\DBAL\Types\JsonbType;
use EonX\EasyCore\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * @covers \EonX\EasyCore\Doctrine\DBAL\Types\JsonbType
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
                    'phpValue' => (object)['property' => 'value'],
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
                ],
                'postgresValue' => '{"key1":"value1","key2":false,"key3":"15","key4":15,"key5":[112,242,309,310]}',
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
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" to Doctrine Type jsonb');
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
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(
            AbstractPlatform::class,
            static function (MockInterface $mock) use ($type): void {
                $mock
                    ->shouldReceive('getDoctrineTypeMapping')
                    ->once()
                    ->with($type::TYPE_NAME)
                    ->andReturns($type::TYPE_NAME);
            }
        );

        $result = $type->getSQLDeclaration([], $platform);

        self::assertSame($type::TYPE_NAME, $result);
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
