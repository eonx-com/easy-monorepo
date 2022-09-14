<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

final class UrlStringSanitizer extends AbstractStringSanitizer
{
    /**
     * @param mixed[] $keysToMask
     */
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            $string = (string)\preg_replace(
                \sprintf('/(%s=|\[%s\]=)([^&]+)/i', $key, $key),
                '$1' . $maskPattern,
                $string
            );
        }

        return $string;
    }
}
