<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType
 */
final class DateTimeMicrosecondsTypeTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType(DateTimeMicrosecondsType::TYPE_NAME, DateTimeMicrosecondsType::class);
    }

    /**
     * @return iterable<mixed>
     *
     * @see testConvertToDatabaseValueSucceeds
     */
    public static function provideConvertToDatabaseValues(): iterable
    {
        $datetime = new DateTime();
        yield 'null value' => [null, null];
        yield 'datetime value' => [$datetime, $datetime->format(DateTimeMicrosecondsType::FORMAT_PHP_DATETIME)];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testConvertToPHPValueSucceeds
     */
    public static function provideConvertToPHPValues(): iterable
    {
        $datetime = new DateTime();
        $milliseconds = $datetime->format('u');
        yield 'null value' => [null, null];
        yield 'DateTimeInterface object' => [$datetime, $datetime];
        yield 'datetime string with milliseconds' => [
            $datetime->format(DateTimeMicrosecondsType::FORMAT_PHP_DATETIME),
            $datetime,
        ];
        yield 'datetime string' => [
            $datetime->format('Y-m-d H:i:s'),
            (clone $datetime)->modify("-{$milliseconds} microsecond"),
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetSqlDeclarationSucceeds
     */
    public static function provideFieldDeclarationValues(): iterable
    {
        yield 'mysql' => [MySQLPlatform::class, [], 'DATETIME(6)'];
        yield 'mysql, with version = true' => [
            MySQLPlatform::class,
            [
                'version' => true,
            ],
            DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP,
        ];
        yield 'mysql, with version = false' => [
            MySQLPlatform::class,
            [
                'version' => false,
            ],
            DateTimeMicrosecondsType::FORMAT_DB_DATETIME,
        ];
        yield 'postgresql' => [
            PostgreSQL94Platform::class,
            [],
            DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];
        yield 'postgresql, with version = true' => [
            PostgreSQL94Platform::class,
            [
                'version' => true,
            ],
            DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];
        yield 'postgresql, with version = false' => [
            PostgreSQL94Platform::class,
            [
                'version' => false,
            ],
            DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds(mixed $value, ?string $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' to type datetime. " .
            'Expected one of the following types: null, DateTime');

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @dataProvider provideConvertToPHPValues
     */
    public function testConvertToPHPValueSucceeds(mixed $value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToPHPValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type datetime. Expected format: Y-m-d H:i:s.u');

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);

        $name = $type->getName();

        self::assertSame(DateTimeMicrosecondsType::TYPE_NAME, $name);
    }

    /**
     * @param mixed[] $fieldDeclaration
     *
     * @dataProvider provideFieldDeclarationValues
     */
    public function testGetSqlDeclarationSucceeds(
        string $platformClass,
        array $fieldDeclaration,
        string $declaration,
    ): void {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }
}
