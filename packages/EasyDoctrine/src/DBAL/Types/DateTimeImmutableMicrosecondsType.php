<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\DBAL\Types;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class DateTimeImmutableMicrosecondsType extends DateTimeImmutableType
{
    private static ?DateTimeZone $utc = null;

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
     * @var string
     */
    public const TYPE_NAME = 'datetime';

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

        if ($value instanceof DateTimeImmutable || $value instanceof DateTime) {
            return $value->setTimezone(self::getUtc())->format(self::FORMAT_PHP_DATETIME);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTimeImmutable']);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        $val = DateTimeImmutable::createFromFormat(self::FORMAT_PHP_DATETIME, $value, self::getUtc())
            ?: \date_create_immutable($value, self::getUtc());

        if ($val !== false) {
            return $val;
        }

        throw ConversionException::conversionFailedFormat(
            $value,
            $this->getName(),
            self::FORMAT_PHP_DATETIME,
        );
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
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

    private static function getUtc(): DateTimeZone
    {
        if (self::$utc === null) {
            self::$utc = new DateTimeZone('UTC');
        }

        return self::$utc;
    }
}
