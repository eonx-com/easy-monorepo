<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

use UnexpectedValueException;

final class StringMaskHelper
{
    private const MASKING_SYMBOL_DEFAULT = '*';

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
     * @param positive-int $visible
     */
    public static function maskMiddle(string $value, int $visible, ?string $maskingSymbol = null): string
    {
        if ($visible < 1) {
            throw new UnexpectedValueException('Visible characters must be a positive integer.');
        }

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

    /**
     * Masks a phone number, showing only the first 1 and last 4 characters. (AWS Cognito consistent)
     */
    public static function maskPhoneNumber(string $phoneNumber): string
    {
        $length = \mb_strlen($phoneNumber);
        if ($length < 5) {
            throw new UnexpectedValueException('Phone number must be at least 5 characters long.');
        }

        return $phoneNumber[0]
            . \str_repeat(self::MASKING_SYMBOL_DEFAULT, $length - 5)
            . \mb_substr($phoneNumber, -4);
    }
}
