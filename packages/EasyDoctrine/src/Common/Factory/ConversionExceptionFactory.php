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

        $stringValue = match (true) {
            \is_string($value) => $value,
            \is_int($value), \is_float($value) => (string)$value,
            \is_bool($value) => $value ? '1' : '0',
            $value === null => '',
            default => \get_debug_type($value),
        };
        $formattedValue = \strlen($stringValue) > 32 ? \substr($stringValue, 0, 20) . '...' : $stringValue;

        return new ConversionException(
            \sprintf(
                'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                $formattedValue,
                $toType,
                $expectedFormat,
            ),
            0,
            $previous,
        );
    }

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

        if (\is_scalar($value) || $value === null) {
            return new ConversionException(
                \sprintf(
                    'Could not convert PHP value %s to type %s. Expected one of the following types: %s',
                    \var_export($value, true),
                    $toType,
                    \implode(', ', $possibleTypes),
                ),
                0,
                $previous,
            );
        }

        return new ConversionException(
            \sprintf(
                'Could not convert PHP value of type %s to type %s. Expected one of the following types: %s',
                \get_debug_type($value),
                $toType,
                \implode(', ', $possibleTypes),
            ),
            0,
            $previous,
        );
    }
}
