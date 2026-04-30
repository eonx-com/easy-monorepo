<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

use UnexpectedValueException;

final class StringMaskHelper
{
    private const MASKING_SYMBOL_DEFAULT = '*';

    public static function maskEmail(string $email): string
    {
        if (\str_contains($email, '@') === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Invalid email address provided.');
            // @codeCoverageIgnoreEnd
        }

        [$local, $domain] = \explode('@', $email);
        $length = \strlen($local);
        $visible = $length === 1 ? 0 : \min(2, $length - 1);

        return \substr($local, 0, $visible)
            . \str_repeat(self::MASKING_SYMBOL_DEFAULT, $length - $visible)
            . '@' . $domain;
    }

    /**
     * @param positive-int $visible
     */
    public static function maskMiddle(string $value, int $visible, ?string $maskingSymbol = null): string
    {
        if ($visible < 1) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Visible characters must be a positive integer.');
            // @codeCoverageIgnoreEnd
        }

        $maskingSymbol ??= self::MASKING_SYMBOL_DEFAULT;

        $length = \strlen($value);

        if ($length <= ($visible * 2)) {
            return \str_repeat($maskingSymbol, $length);
        }

        return \substr($value, 0, $visible)
            . \str_repeat($maskingSymbol, $length - ($visible * 2))
            . \substr($value, -$visible);
    }

    /**
     * Masks a phone number, showing only the first and last 4 characters. (AWS Cognito consistent))
     */
    public static function maskPhoneNumber(string $phoneNumber): string
    {
        $length = \strlen($phoneNumber);
        if ($length < 5) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Phone number must be at least 5 characters long.');
            // @codeCoverageIgnoreEnd
        }

        return $phoneNumber[0]
            . \str_repeat(self::MASKING_SYMBOL_DEFAULT, $length - 5)
            . \substr($phoneNumber, -4);
    }
}
