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
            $string = $this->maskNonStringValuesInJsonString($string, $key, $maskPattern);
            $string = $this->maskNonStringValuesInEscapedJsonString($string, $key, $maskPattern);
            $string = $this->maskStringValuesInJsonString($string, $key, $maskPattern);
        }

        return $string;
    }

    private function maskNonStringValuesInEscapedJsonString(
        string $escapedJsonString,
        string $key,
        string $maskPattern,
    ): string {
        return (string)\preg_replace(
            \sprintf('/(\\\"%s\\\"\s*:\s*(?!\s*("|\\\"))(\[)?)(?(?<=\[)([^\]]+)|([^,}]+[}]?))(\]|,|})/i', $key),
            \sprintf('$1\"%s\"$6', $maskPattern),
            $escapedJsonString
        );
    }

    private function maskNonStringValuesInJsonString(string $jsonString, string $key, string $maskPattern): string
    {
        return (string)\preg_replace(
            \sprintf('/("%s"\s*:\s*(?!\s*("|\\\"))(\[)?)(?(?<=\[)([^\]]+)|([^,}]+[}]?))(\]|,|})/i', $key),
            \sprintf('$1"%s"$6', $maskPattern),
            $jsonString
        );
    }

    private function maskStringValuesInJsonString(string $jsonString, string $key, string $maskPattern): string
    {
        return (string)\preg_replace(
            \sprintf('/((\\\)?"%s(\\\)?"\s*:\s*(\\\)?")([^\\\"]+)("|\\\")/i', $key),
            \sprintf('$1%s$6', $maskPattern),
            $jsonString
        );
    }
}
