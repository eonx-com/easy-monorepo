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
        '40309', //Value of the CURLOPT_CAINFO_BLOB constant,
        '40291', //Value of the CURLOPT_SSLCERT_BLOB constant,
        '40292', //Value of the CURLOPT_SSLKEY_BLOB constant,
    ];

    public const DEFAULT_MASK_PATTERN = '*REDACTED*';

    public function sanitize(mixed $data): mixed;
}
