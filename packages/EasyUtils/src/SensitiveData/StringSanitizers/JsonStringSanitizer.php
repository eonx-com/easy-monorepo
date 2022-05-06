<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

final class JsonStringSanitizer extends AbstractStringSanitizer
{
    /**
     * @param mixed[] $keysToMask
     */
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            $string = (string)\preg_replace(
                \sprintf('/(\\\"%s\\\"\s*:\s*\\\"|"%s"\s*:\s*")([^\\\"]+)(\\\"|")/', $key, $key),
                '$1' . $maskPattern . '$3',
                $string
            );
        }

        return $string;
    }
}
