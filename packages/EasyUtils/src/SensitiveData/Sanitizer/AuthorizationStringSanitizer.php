<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

final class AuthorizationStringSanitizer extends AbstractStringSanitizer
{
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string
    {
        return (string)\preg_replace(
            '/(^|\b)(Authorization:)[^\r\n]+/mi',
            '$2 ' . $maskPattern,
            $string
        );
    }
}
