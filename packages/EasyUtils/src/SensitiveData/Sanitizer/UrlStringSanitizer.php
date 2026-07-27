<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            // Query/array style (key=..., [key]=...): the value ends at &, ? or #, but may
            // legitimately contain "/" (e.g. base64 tokens), so "/" must NOT terminate it
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=)([^&?#]+)/i', $key, $key),
                '$1' . $maskPattern,
                $string
            );

            // Path style (/key/...): the value is a single path segment, so "/" terminates it
            $string = (string)\preg_replace(
                \sprintf('/(\/%s\/)([^&\/?#]+)/i', $key),
                '$1' . $maskPattern,
                $string
            );
        }

        return $string;
    }
}
