<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

final class JsonStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        foreach ($keysToMask as $key) {
            $string = (string)\preg_replace(
                \sprintf(
                    '/((\\\)?"%s(\\\)?"\s*:\s*(\\\)?(\\[|"))(?(?<=\\[)([^\\]]+)|([^\\\"]+))(\\\"|"|\\])/i',
                    $key
                ),
                '$1' . $maskPattern . '$8',
                $string
            );
        }

        return $string;
    }
}
