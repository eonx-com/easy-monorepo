<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Factory;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Throwable;

/**
 * @deprecated Remove when Doctrine DBAL 3 support is dropped.
 */
final class ConversionExceptionFactory
{
    /**
     * @param array<string> $possibleTypes
     */
    public static function invalidType(
        mixed $value,
        string $toType,
        array $possibleTypes,
        ?Throwable $previous = null,
    ): ConversionException {
        if (\class_exists(InvalidType::class)) {
            /** @var class-string $exceptionClass */
            $exceptionClass = InvalidType::class;

            return $exceptionClass::new($value, $toType, $possibleTypes, $previous);
        }

        return ConversionException::conversionFailedInvalidType($value, $toType, $possibleTypes, $previous);
    }

    public static function invalidFormat(
        mixed $value,
        string $toType,
        string $expectedFormat,
        ?Throwable $previous = null,
    ): ConversionException {
        if (\class_exists(InvalidFormat::class)) {
            /** @var class-string $exceptionClass */
            $exceptionClass = InvalidFormat::class;

            return $exceptionClass::new($value, $toType, $expectedFormat, $previous);
        }

        return ConversionException::conversionFailedFormat($value, $toType, $expectedFormat, $previous);
    }
}
