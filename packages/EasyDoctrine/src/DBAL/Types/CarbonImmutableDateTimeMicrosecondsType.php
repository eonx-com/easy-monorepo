<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\DBAL\Types;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class CarbonImmutableDateTimeMicrosecondsType extends DateTimeImmutableType
{
    /**
     * @var string
     */
    public const FORMAT_DB_DATETIME = 'DATETIME(6)';

    /**
     * @var string
     */
    public const FORMAT_DB_TIMESTAMP = 'TIMESTAMP';

    /**
     * @var string
     */
    public const FORMAT_DB_TIMESTAMP_WO_TIMEZONE = 'TIMESTAMP(6) WITHOUT TIME ZONE';

    /**
     * @var string
     */
    public const FORMAT_PHP_DATETIME = 'Y-m-d H:i:s.u';

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(self::FORMAT_PHP_DATETIME);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTimeInterface']);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?CarbonImmutable
    {
        if ($value === null || $value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        $dateTime = DateTimeImmutable::createFromFormat(self::FORMAT_PHP_DATETIME, $value);

        if ($dateTime === false) {
            $dateTime = \date_create_immutable($value);
        }

        if ($dateTime instanceof DateTimeInterface) {
            return CarbonImmutable::instance($dateTime);
        }

        throw ConversionException::conversionFailedFormat($value, $this->getName(), self::FORMAT_PHP_DATETIME);
    }

    /**
     * @param mixed[] $fieldDeclaration
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQL94Platform) {
            return self::FORMAT_DB_TIMESTAMP_WO_TIMEZONE;
        }

        if (isset($fieldDeclaration['version']) && $fieldDeclaration['version'] === true) {
            return self::FORMAT_DB_TIMESTAMP;
        }

        return self::FORMAT_DB_DATETIME;
    }
}
