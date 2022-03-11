<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType
 */
final class CarbonImmutableDateTimeMicrosecondsTypeTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testConvertToDatabaseValueSucceeds
     */
    public function provideConvertToDatabaseValues(): iterable
    {
        $datetime = new DateTimeImmutable();

        yield 'null value' => [null, null];

        yield 'datetime value' => [
            $datetime,
            $datetime->format(CarbonImmutableDateTimeMicrosecondsType::FORMAT_PHP_DATETIME),
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testConvertToPHPValueSucceeds
     */
    public function provideConvertToPHPValues(): iterable
    {
        $datetime = new DateTimeImmutable();
        $milliseconds = $datetime->format('u');

        yield 'null value' => [null, null];

        yield 'DateTimeInterface object' => [$datetime, $datetime];

        yield 'datetime string with milliseconds' => [
            $datetime->format(CarbonImmutableDateTimeMicrosecondsType::FORMAT_PHP_DATETIME),
            $datetime,
        ];

        yield 'datetime string' => [
            $datetime->format('Y-m-d H:i:s'),
            $datetime->modify("-{$milliseconds} microsecond"),
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetSqlDeclarationSucceeds
     */
    public function provideFieldDeclarationValues(): iterable
    {
        yield 'mysql' => [MySqlPlatform::class, [], 'DATETIME(6)'];

        yield 'mysql, with version = true' => [
            MySqlPlatform::class,
            [
                'version' => true,
            ],
            CarbonImmutableDateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP,
        ];

        yield 'mysql, with version = false' => [
            MySqlPlatform::class,
            [
                'version' => false,
            ],
            CarbonImmutableDateTimeMicrosecondsType::FORMAT_DB_DATETIME,
        ];

        $platformClassNameDbal2 = '\Doctrine\DBAL\Platforms\PostgreSQL94Platform';
        $platformClassNameDbal3 = '\Doctrine\DBAL\Platforms\PostgreSQLPlatform';
        $platformClassName = class_exists($platformClassNameDbal2) ? $platformClassNameDbal2 : $platformClassNameDbal3;

        yield 'postgresql' => [
            $platformClassName,
            [],
            CarbonImmutableDateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];

        yield 'postgresql, with version = true' => [
            $platformClassName,
            [
                'version' => true,
            ],
            CarbonImmutableDateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];

        yield 'postgresql, with version = false' => [
            $platformClassName,
            [
                'version' => false,
            ],
            CarbonImmutableDateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($value, ?string $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' " .
            "of type 'string' to type 'datetime_immutable'. " .
            'Expected one of the following types: null, DateTimeInterface');

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideConvertToPHPValues
     */
    public function testConvertToPHPValueSucceeds($value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPHPValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type datetime_immutable. Expected format: Y-m-d H:i:s.u');

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());

        $name = $type->getName();

        self::assertSame(Types::DATETIME_IMMUTABLE, $name);
    }

    /**
     * @param mixed[] $fieldDeclaration
     *
     * @dataProvider provideFieldDeclarationValues
     */
    public function testGetSqlDeclarationSucceeds(
        string $platformClass,
        array $fieldDeclaration,
        string $declaration
    ): void {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateTimeMicrosecondsType $type */
        $type = Type::getType((new CarbonImmutableDateTimeMicrosecondsType())->getName());
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType(
            (new CarbonImmutableDateTimeMicrosecondsType())->getName(),
            CarbonImmutableDateTimeMicrosecondsType::class
        );
    }
}
