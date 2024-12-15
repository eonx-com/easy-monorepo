<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Type;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\ConversionException;
use EonX\EasyDoctrine\Common\Type\CarbonImmutableDateTimeMicrosecondsType;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CarbonImmutableDateTimeMicrosecondsType::class)]
final class CarbonImmutableDateTimeMicrosecondsTypeTest extends AbstractUnitTestCase
{
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
            new CarbonImmutable('2022-01-01T10:00:00+09:00'),
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
            'TIMESTAMP',
        ];

        yield 'mysql, with version = false' => [
            MySQLPlatform::class,
            [
                'version' => false,
            ],
            'DATETIME(6)',
        ];

        yield 'postgresql' => [
            PostgreSQLPlatform::class,
            [],
            'TIMESTAMP(6) WITHOUT TIME ZONE',
        ];

        yield 'postgresql, with version = true' => [
            PostgreSQLPlatform::class,
            [
                'version' => true,
            ],
            'TIMESTAMP(6) WITHOUT TIME ZONE',
        ];

        yield 'postgresql, with version = false' => [
            PostgreSQLPlatform::class,
            [
                'version' => false,
            ],
            'TIMESTAMP(6) WITHOUT TIME ZONE',
        ];
    }

    #[DataProvider('provideConvertToDatabaseValues')]
    public function testConvertToDatabaseValueSucceeds(mixed $value, ?string $expectedValue = null): void
    {
        $type = new CarbonImmutableDateTimeMicrosecondsType();
        $platform = new SqlitePlatform();

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        $type = new CarbonImmutableDateTimeMicrosecondsType();
        $platform = new SqlitePlatform();
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' " .
            'to type datetime_immutable. Expected one of the following types: null, DateTimeInterface');

        $type->convertToDatabaseValue($value, $platform);
    }

    #[DataProvider('provideConvertToPHPValues')]
    public function testConvertToPHPValueSucceeds(
        DateTimeInterface|string|null $value = null,
        ?DateTimeInterface $expectedValue = null,
    ): void {
        $type = new CarbonImmutableDateTimeMicrosecondsType();
        $platform = new SqlitePlatform();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPHPValueThrowsConversionException(): void
    {
        $type = new CarbonImmutableDateTimeMicrosecondsType();
        $platform = new SqlitePlatform();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type datetime_immutable. Expected format: Y-m-d H:i:s.u');

        $type->convertToPHPValue($value, $platform);
    }

    /**
     * @param class-string<\Doctrine\DBAL\Platforms\AbstractPlatform> $platformClass
     */
    #[DataProvider('provideFieldDeclarationValues')]
    public function testGetSqlDeclarationSucceeds(
        string $platformClass,
        array $fieldDeclaration,
        string $declaration,
    ): void {
        $type = new CarbonImmutableDateTimeMicrosecondsType();
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }
}
