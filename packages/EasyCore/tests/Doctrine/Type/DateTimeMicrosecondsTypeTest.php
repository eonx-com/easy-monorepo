<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Type;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
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
            'datetime value' => [$datetime, $datetime->format('Y-m-d H:i:s.u')],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideFieldDeclarationValues(): array
    {
        return [
            'no version' => [[], 'DATETIME(6)'],
            'version=false' => [['version' => false], 'DATETIME(6)'],
            'version=true' => [['version' => true], 'TIMESTAMP'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function provideConvertToPHPValues(): array
    {
        $datetime = new DateTime();
        $milliseconds = $datetime->format('u');

        return [
            'null value' => [null, null],
            'DateTimeInterface object' => [$datetime, $datetime],
            'datetime string with milliseconds' => [$datetime->format('Y-m-d H:i:s.u'), $datetime],
            'datetime string' => [$datetime->format('Y-m-d H:i:s'), (clone $datetime)->modify("-{$milliseconds} microsecond")],
        ];
    }

    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        $type = new DateTimeMicrosecondsType();
        /** @var AbstractPlatform $platform */
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
     * @throws \Doctrine\DBAL\Types\ConversionException
     *
     * @dataProvider provideConvertToDatabaseValues
     */
    public function testConvertToDatabaseValueSucceeds($value, ?string $expectedValue): void
    {
        $type = new DateTimeMicrosecondsType();
        /** @var AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $databaseValue = $type->convertToDatabaseValue($value, $platform);

        self::assertSame($expectedValue, $databaseValue);
    }

    public function testGetNameSucceeds(): void
    {
        $type = new DateTimeMicrosecondsType();

        $name = $type->getName();

        self::assertSame('datetime', $name);
    }

    /**
     * @dataProvider provideFieldDeclarationValues
     *
     * @param mixed[] $fieldDeclaration
     */
    public function testGetSQLDeclarationSucceeds(array $fieldDeclaration, string $declaration): void
    {
        $type = new DateTimeMicrosecondsType();
        /** @var AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $actualDeclaration = $type->getSQLDeclaration($fieldDeclaration, $platform);

        self::assertSame($declaration, $actualDeclaration);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideConvertToPHPValues
     */
    public function testConvertToPHPValueSucceeds($value, ?DateTimeInterface $expectedValue): void
    {
        $type = new DateTimeMicrosecondsType();
        /** @var AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }

    public function testConvertToPHPValueThrowsConversionException(): void
    {
        $type = new DateTimeMicrosecondsType();
        /** @var AbstractPlatform $platform */
        $platform = $this->mock(AbstractPlatform::class);
        $value = 'some-ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "some-ineligible-value" ' .
            'to Doctrine Type datetime. Expected format: Y-m-d H:i:s.u');

        $type->convertToPHPValue($value, $platform);
    }
}
