<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

interface SensitiveDataSanitizerInterface
{
    public const CURLOPT_CAINFO_BLOB = 40309;

    public const CURLOPT_SSLCERT_BLOB = 40291;

    public const CURLOPT_SSLKEY_BLOB = 40292;

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
        self::CURLOPT_CAINFO_BLOB,
        self::CURLOPT_SSLCERT_BLOB,
        self::CURLOPT_SSLKEY_BLOB,
    ];

    public const DEFAULT_MASK_PATTERN = '*REDACTED*';

    public function sanitize(mixed $data): mixed;
}
