<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\DBAL\Types;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
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

    private static ?DateTimeZone $utc = null;

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof DateTimeImmutable || $value instanceof DateTime) {
            return $value->setTimezone(self::getUtc())->format(self::FORMAT_PHP_DATETIME);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTimeInterface']);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CarbonImmutable
    {
        if ($value === null || $value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        $dateTime = DateTimeImmutable::createFromFormat(self::FORMAT_PHP_DATETIME, $value, self::getUtc());

        if ($dateTime === false) {
            $dateTime = \date_create_immutable($value, self::getUtc());
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
        $platformClassNameDbal2 = PostgreSQL94Platform::class;
        $platformClassNameDbal3 = PostgreSQLPlatform::class;
        if (\is_a($platform, $platformClassNameDbal3) || \is_a($platform, $platformClassNameDbal2)) {
            return self::FORMAT_DB_TIMESTAMP_WO_TIMEZONE;
        }

        if (isset($fieldDeclaration['version']) && $fieldDeclaration['version'] === true) {
            return self::FORMAT_DB_TIMESTAMP;
        }

        return self::FORMAT_DB_DATETIME;
    }

    private static function getUtc(): DateTimeZone
    {
        if (self::$utc === null) {
            self::$utc = new DateTimeZone('UTC');
        }

        return self::$utc;
    }
}
