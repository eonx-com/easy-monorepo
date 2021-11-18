<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
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
            $datetime->format(DateTimeImmutableMicrosecondsType::FORMAT_PHP_DATETIME),
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
            $datetime->format(DateTimeImmutableMicrosecondsType::FORMAT_PHP_DATETIME),
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
            DateTimeImmutableMicrosecondsType::FORMAT_DB_TIMESTAMP,
        ];

        yield 'mysql, with version = false' => [
            MySqlPlatform::class,
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

    /**
     * @param mixed $value
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
            "of type 'string' to type 'datetime'. " .
            'Expected one of the following types: null, DateTimeImmutable');

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideConvertToPHPValues
     */
    public function testConvertToPHPValueSucceeds($value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPHPValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
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
        /** @var \EonX\EasyDoctrine\DBAL\Types\DateTimeImmutableMicrosecondsType $type */
        $type = Type::getType(DateTimeImmutableMicrosecondsType::TYPE_NAME);

        $name = $type->getName();

        self::assertSame(DateTimeImmutableMicrosecondsType::TYPE_NAME, $name);
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
