<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

final class AuthorizationStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        return (string)\preg_replace(
            '/(Authorization:)[A-Za-z0-9=_\-\. ]+/',
            '$1' . $maskPattern,
            $string
        );
    }
}
