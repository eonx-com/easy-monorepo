<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Type;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
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
            'no version' => [[], DateTimeMicrosecondsType::FORMAT_DB_DATETIME],
            'version=false' => [['version' => false], DateTimeMicrosecondsType::FORMAT_DB_DATETIME],
            'version=true' => [['version' => true], DateTimeMicrosecondsType::FORMAT_DB_TIMESTAMP],
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
        $type = Type::getType('datetime');
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
        $type = Type::getType('datetime');
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType('datetime');

        $name = $type->getName();

        self::assertSame('datetime', $name);
    }

    /**
     * @dataProvider provideFieldDeclarationValues
     *
     * @param mixed[] $fieldDeclaration
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetSqlDeclarationSucceeds(array $fieldDeclaration, string $declaration): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType('datetime');
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

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
        $type = Type::getType('datetime');
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $phpValue = $type->convertToPhpValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyCore\Doctrine\Type\DateTimeMicrosecondsType $type */
        $type = Type::getType('datetime');
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "some-ineligible-value" ' .
            'to Doctrine Type datetime. Expected format: Y-m-d H:i:s.u');

        $type->convertToPhpValue($value, $platform);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType('datetime', DateTimeMicrosecondsType::class);
    }
}
