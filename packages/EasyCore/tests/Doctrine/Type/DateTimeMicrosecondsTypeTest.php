<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Type;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType
 */
final class DateTimeMicrosecondsTypeTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     */
    public function provideConvertToDatabaseValues(): array
    {
        $datetime = new DateTime();

        return [
            'null value' => [null, null],
            'datetime value' => [$datetime, $datetime->format(DateTimeMicrosecondsType::FORMAT_PHP_DATETIME)],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideFieldDeclarationValues(): array
    {
        return [
            'mysql' => [
                MySqlPlatform::class,
                [],
                'DATETIME(6)',
            ],
            'mysql, with version = true' => [
                MySqlPlatform::class,
                ['version' => true],
                DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP,
            ],
            'mysql, with version = false' => [
                MySqlPlatform::class,
                ['version' => false],
                DateTimeMicrosecondsType::FORMAT_DB_DATETIME,
            ],
            'postgresql' => [
                PostgreSqlPlatform::class,
                [],
                DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
            'postgresql, with version = true' => [
                PostgreSqlPlatform::class,
                ['version' => true],
                DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
            'postgresql, with version = false' => [
                PostgreSqlPlatform::class,
                ['version' => false],
                DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP_WO_TIMEZONE,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideConvertToPhpValues(): array
    {
        $datetime = new DateTime();
        $milliseconds = $datetime->format('u');

        return [
            'null value' => [null, null],
            'DateTimeInterface object' => [$datetime, $datetime],
            'datetime string with milliseconds' => [$datetime->format(DateTimeMicrosecondsType::FORMAT_PHP_DATETIME), $datetime],
            'datetime string' => [$datetime->format('Y-m-d H:i:s'), (clone $datetime)->modify("-{$milliseconds} microsecond")],
        ];
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage("Could not convert PHP value 'some-ineligible-value' " .
            "of type 'string' to type 'datetime'. Expected one of the following types: null, DateTime");

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($value, ?string $expectedValue): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);

        $name = $type->getName();

        self::assertSame(DateTimeMicrosecondsType::TYPE_NAME, $name);
    }

    /**
     * @param mixed[] $fieldDeclaration
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @dataProvider provideFieldDeclarationValues
     */
    public function testGetSqlDeclarationSucceeds(string $platformClass, array $fieldDeclaration, string $declaration): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = new $platformClass();

        $actualDeclaration = $type->getSqlDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Types\ConversionException
     *
     * @dataProvider provideConvertToPhpValues
     */
    public function testConvertToPhpValueSucceeds($value, ?DateTimeInterface $expectedValue): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $phpValue = $type->convertToPhpValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType(DateTimeMicrosecondsType::TYPE_NAME);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" ' .
            'to Doctrine Type datetime. Expected format: Y-m-d H:i:s.u');

        $type->convertToPhpValue($value, $platform);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType(DateTimeMicrosecondsType::TYPE_NAME, DateTimeMicrosecondsType::class);
    }
}
