<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Type;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

final class DateTimeMicrosecondsType extends Type
{
    private const TYPE_NAME = 'datetime';

    public const FORMAT_PHP_DATETIME = 'Y-m-d H:i:s.u';

    public const FORMAT_DB_TIMESTAMP = 'TIMESTAMP';

    public const FORMAT_DB_DATETIME = 'DATETIME(6)';

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

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', 'DateTime']
        );
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

        throw ConversionException::conversionFailedFormat(
            $value,
            $this->getName(),
            self::FORMAT_PHP_DATETIME
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
        if (isset($fieldDeclaration['version']) && $fieldDeclaration['version'] === true) {
            return self::FORMAT_DB_TIMESTAMP;
        }

        return self::FORMAT_DB_DATETIME;
    }
}
