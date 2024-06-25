<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Type;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DateTimeImmutableMicrosecondsType::class)]
final class DateTimeImmutableMicrosecondsTypeTest extends AbstractUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType(DateTimeImmutableMicrosecondsType::TYPE_NAME, DateTimeImmutableMicrosecondsType::class);
    }

    /**
     * @see testConvertToDatabaseValueSucceeds
     */
    public static function provideConvertToDatabaseValues(): iterable
    {
        yield 'null value' => [null, null];

        yield 'datetime value' => [
            new DateTimeImmutable('2022-01-01T10:00:00+00:00'),
            '2022-01-01 10:00:00.000000',
        ];

        yield 'datetime value with timezone' => [
            new DateTimeImmutable('2022-01-01T10:00:00+09:00'),
            '2022-01-01 01:00:00.000000',
        ];
    }

    /**
     * @see testConvertToPHPValueSucceeds
     */
    public static function provideConvertToPHPValues(): iterable
    {
        yield 'null value' => [null, null];

        yield 'DateTimeInterface object' => [
            new DateTimeImmutable('2022-01-01 10:00:00.12345'),
            new DateTimeImmutable('2022-01-01 10:00:00.12345'),
        ];

        yield 'datetime string with milliseconds' => [
            '2022-01-01 10:00:00.12345',
            new DateTimeImmutable('2022-01-01 10:00:00.12345', new DateTimeZone('UTC')),
        ];

        yield 'datetime string' => [
            '2022-01-01 10:00:00',
            new DateTimeImmutable('2022-01-01 10:00:00', new DateTimeZone('UTC')),
        ];

        yield 'datetime string with timezone' => [
            '2022-01-01T10:00:00+09:00',
            new DateTimeImmutable('2022-01-01 01:00:00.000000', new DateTimeZone('UTC')),
        ];
    }

    /**
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
            DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP,
        ];

        yield 'mysql, with version = false' => [
            MySQLPlatform::class,
            [
                'version' => false,
            ],
            DateTimeImmutableMicrosecondsType::FORMAT_DB_DATETIME,
        ];

        yield 'postgresql' => [
            PostgreSQL94Platform::class,
            [],
            DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];

        yield 'postgresql, with version = true' => [
            PostgreSQL94Platform::class,
            [
                'version' => true,
            ],
            DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];

        yield 'postgresql, with version = false' => [
            PostgreSQL94Platform::class,
            [
                'version' => false,
            ],
            DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];
    }

    #[DataProvider('provideConvertToDatabaseValues')]
    public function testConvertToDatabaseValueSucceeds(mixed $value, ?string $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' " .
            'to type datetime. Expected one of the following types: null, DateTimeImmutable');

        $type->convertToDatabaseValue($value, $platform);
    }

    #[DataProvider('provideConvertToPHPValues')]
    public function testConvertToPHPValueSucceeds(mixed $value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPHPValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
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
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);

        $name = $type->getName();

        self::assertSame(DateTimeImmutableMicrosecondsType::TYPE_NAME, $name);
    }

    #[DataProvider('provideFieldDeclarationValues')]
    public function testGetSqlDeclarationSucceeds(
        string $platformClass,
        array $fieldDeclaration,
        string $declaration,
    ): void {
        /** @var \EonX\EasyDoctrine\Common\Type\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }
}
