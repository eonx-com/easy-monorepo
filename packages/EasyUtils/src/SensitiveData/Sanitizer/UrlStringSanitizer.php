<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=|\/%s\/)([^&\/?#]+)/i', $key, $key, $key),
                '$1' . $maskPattern,
                $string
            );
        }

        return $string;
    }
}
