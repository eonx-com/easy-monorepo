<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

interface SensitiveDataSanitizerInterface
{
    public const DEFAULT_KEYS_TO_MASK = [
        'access_key',
        'access_secret',
        'access_token',
        'apikey',
        'auth_basic',
        'auth_bearer',
        'authorization',
        'card_number',
        'cert',
        'cvc',
        'cvv',
        'number',
        'password',
        'php-auth-pw',
        'php_auth_pw',
        'php-auth-user',
        'php_auth_user',
        'securitycode',
        'token',
        'verificationcode',
        'x-shared-key',
    ];

    public const DEFAULT_MASK_PATTERN = '*REDACTED*';

    public function sanitize(mixed $data): mixed;
}
