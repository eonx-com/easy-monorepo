<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType
 */
final class DateTimeImmutableMicrosecondsTypeTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testConvertToDatabaseValueSucceeds
     */
    public function provideConvertToDatabaseValues(): array
    {
        $datetime = new DateTimeImmutable();

        return [
            'null value' => [null, null],
            'datetime value' => [
                $datetime,
                $datetime->format(DateTimeImmutableMicrosecondsType::FORMAT_PHP_DATETIME),
            ],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testConvertToPhpValueSucceeds
     */
    public function provideConvertToPhpValues(): array
    {
        $datetime = new DateTimeImmutable();
        $milliseconds = $datetime->format('u');

        return [
            'null value' => [null, null],
            'DateTimeInterface object' => [$datetime, $datetime],
            'datetime string with milliseconds' => [
                $datetime->format(DateTimeImmutableMicrosecondsType::FORMAT_PHP_DATETIME),
                $datetime,
            ],
            'datetime string' => [
                $datetime->format('Y-m-d H:i:s'),
                (clone $datetime)->modify("-{$milliseconds} microsecond"),
            ],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testGetSqlDeclarationSucceeds
     */
    public function provideFieldDeclarationValues(): array
    {
        return [
            'mysql' => [MySqlPlatform::class, [], 'DATETIME(6)'],
            'mysql, with version = true' => [
                MySqlPlatform::class,
                [
                    'version' => true,
                ],
                DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP,
            ],
            'mysql, with version = false' => [
                MySqlPlatform::class,
                [
                    'version' => false,
                ],
                DateTimeImmutableMicrosecondsType::FORMAT_DB_DATETIME,
            ],
            'postgresql' => [
                PostgreSqlPlatform::class,
                [],
                DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
            'postgresql, with version = true' => [
                PostgreSqlPlatform::class,
                [
                    'version' => true,
                ],
                DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
            'postgresql, with version = false' => [
                PostgreSqlPlatform::class,
                [
                    'version' => false,
                ],
                DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
        ];
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($value, ?string $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' " .
            "of type 'string' to type 'datetime_immutable'. " .
            'Expected one of the following types: null, DateTimeImmutable');

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     *
     * @dataProvider provideConvertToPhpValues
     */
    public function testConvertToPhpValueSucceeds($value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $phpValue = $type->convertToPhpValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type datetime_immutable. Expected format: Y-m-d H:i:s.u');

        $type->convertToPhpValue($value, $platform);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);

        $name = $type->getName();

        self::assertSame(DateTimeImmutableMicrosecondsType::TYPE_NAME, $name);
    }

    /**
     * @param mixed[] $fieldDeclaration
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideFieldDeclarationValues
     */
    public function testGetSqlDeclarationSucceeds(
        string $platformClass,
        array $fieldDeclaration,
        string $declaration
    ): void {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType(DateTimeImmutableMicrosecondsType::TYPE_NAME, DateTimeImmutableMicrosecondsType::class);
    }
}
