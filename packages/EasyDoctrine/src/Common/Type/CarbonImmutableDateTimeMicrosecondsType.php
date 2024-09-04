<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Type;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class CarbonImmutableDateTimeMicrosecondsType extends DateTimeImmutableType
{
    private const FORMAT_DB_DATETIME = 'DATETIME(6)';

    private const FORMAT_DB_TIMESTAMP = 'TIMESTAMP';

    private const FORMAT_DB_TIMESTAMP_WO_TIMEZONE = 'TIMESTAMP(6) WITHOUT TIME ZONE';

    private const FORMAT_PHP_DATETIME = 'Y-m-d H:i:s.u';

    private static ?DateTimeZone $utc = null;

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeImmutable || $value instanceof DateTime) {
            return $value->setTimezone(self::getUtc())->format(self::FORMAT_PHP_DATETIME);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTimeInterface']);
    }

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

    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return self::FORMAT_DB_TIMESTAMP_WO_TIMEZONE;
        }

        if (isset($column['version']) && $column['version'] === true) {
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
