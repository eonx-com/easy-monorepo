<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            // Keys come from configuration and are interpolated into the pattern, so escape any
            // regex metacharacters (and the "/" delimiter) to keep matching literal and safe
            $quotedKey = \preg_quote((string)$key, '/');

            // Query/array style (key=..., [key]=...): the value ends at &, ? or #, but may
            // legitimately contain "/" (e.g. base64 tokens), so "/" must NOT terminate it
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=)([^&?#]+)/i', $quotedKey, $quotedKey),
                '$1' . $maskPattern,
                $string
            );

            // Path style (/key/...): the value is a single path segment, so "/" terminates it
            $string = (string)\preg_replace(
                \sprintf('/(\/%s\/)([^&\/?#]+)/i', $quotedKey),
                '$1' . $maskPattern,
                $string
            );
        }

        return $string;
    }
}
