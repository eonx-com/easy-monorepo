<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

use UnexpectedValueException;

final class StringMaskHelper
{
    private const string MASKING_SYMBOL_DEFAULT = '*';

    public static function maskEmail(string $email, ?string $maskingSymbol = null): string
    {
        if (\str_contains($email, '@') === false) {
            throw new UnexpectedValueException('Invalid email address provided.');
        }

        [$local, $domain] = \explode('@', $email);
        $length = \mb_strlen($local);
        $visible = $length === 1 ? 0 : \min(2, $length - 1);

        $maskingSymbol ??= self::MASKING_SYMBOL_DEFAULT;

        return \mb_substr($local, 0, $visible)
            . \str_repeat($maskingSymbol, $length - $visible)
            . '@' . $domain;
    }

    /**
     * @param positive-int $maskLength
     */
    public static function maskFirst(string $value, int $maskLength, ?string $maskingSymbol = null): string
    {
        $maskingSymbol ??= self::MASKING_SYMBOL_DEFAULT;

        $maskLength = \min($maskLength, \mb_strlen($value));
        $mask = \str_repeat($maskingSymbol, $maskLength);

        return $mask . \mb_substr($value, $maskLength);
    }

    /**
     * @param positive-int $visible
     */
    public static function maskMiddle(string $value, int $visible, ?string $maskingSymbol = null): string
    {
        $maskingSymbol ??= self::MASKING_SYMBOL_DEFAULT;

        $length = \mb_strlen($value);
        if ($length === 0) {
            return $value;
        }

        if ($length <= ($visible * 2)) {
            return \str_repeat($maskingSymbol, $length);
        }

        return \mb_substr($value, 0, $visible)
            . \str_repeat($maskingSymbol, $length - ($visible * 2))
            . \mb_substr($value, -$visible);
    }
}
