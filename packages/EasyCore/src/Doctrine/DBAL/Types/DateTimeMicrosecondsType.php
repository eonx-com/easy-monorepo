<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\DBAL\Types;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * @deprecated since 3.5, will be removed in 4.0. Use EasyDoctrine instead.
 */
final class DateTimeMicrosecondsType extends DateTimeType
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

        if ($value instanceof DateTimeInterface) {
            return $value->format(self::FORMAT_PHP_DATETIME);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPhpValue($value, AbstractPlatform $platform): ?DateTimeInterface
    {
        if ($value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        $val = DateTime::createFromFormat(self::FORMAT_PHP_DATETIME, $value) ?: \date_create($value);

        if ($val !== false) {
            return $val;
        }

        throw ConversionException::conversionFailedFormat($value, $this->getName(), self::FORMAT_PHP_DATETIME);
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
        if ($platform instanceof PostgreSqlPlatform) {
            return self::FORMAT_DB_TIMESTAMP_WO_TIMEZONE;
        }

        if (isset($fieldDeclaration['version']) && $fieldDeclaration['version'] === true) {
            return self::FORMAT_DB_TIMESTAMP;
        }

        return self::FORMAT_DB_DATETIME;
    }
}
