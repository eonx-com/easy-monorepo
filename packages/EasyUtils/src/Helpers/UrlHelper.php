<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

final class UrlHelper
{
    public static function urlSafeBase64Decode(string $input): string
    {
        $remainder = \strlen($input) % 4;

        if ($remainder !== 0) {
            $padLen = 4 - $remainder;
            $input .= \str_repeat('=', $padLen);
        }

        return \base64_decode(\strtr($input, '-_', '+/'), true) ?: '';
    }

    public static function urlSafeBase64Encode(string $input): string
    {
        return \str_replace('=', '', \strtr(\base64_encode($input), '+/', '-_'));
    }
}
