<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_MATH_FORMAT_DECIMAL_SEPARATOR = 'easy_utils.math.format_decimal_separator';

    public const PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR = 'easy_utils.math.format_thousands_separator';

    public const PARAM_MATH_ROUND_MODE = 'easy_utils.math.round_mode';

    public const PARAM_MATH_ROUND_PRECISION = 'easy_utils.math.round_precision';

    public const PARAM_MATH_SCALE = 'easy_utils.math.scale';

    public const PARAM_SENSITIVE_DATA_KEYS_TO_MASK = 'easy_utils.sensitive_data.keys_to_mask';

    public const PARAM_SENSITIVE_DATA_MASK_PATTERN = 'easy_utils.sensitive_data.mask_pattern';

    /**
     * @deprecated
     */
    public const PARAM_SENSITIVE_DATA_USE_DEFAULT_KEYS_TO_MASK = 'easy_utils.sensitive_data.use_default_keys_to_mask';

    public const PARAM_STRING_TRIMMER_EXCEPT_KEYS = 'easy_utils.string_trimmer.except_keys';

    public const SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK = [
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
        '40309', // Value of the CURLOPT_CAINFO_BLOB constant
        '40291', // Value of the CURLOPT_SSLCERT_BLOB constant
        '40292', // Value of the CURLOPT_SSLKEY_BLOB constant
    ];

    public const SENSITIVE_DATA_DEFAULT_MASK_PATTERN = '*REDACTED*';

    public const TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER = 'easy_utils.sensitive_data.object_transformer';

    public const TAG_SENSITIVE_DATA_STRING_SANITIZER = 'easy_utils.sensitive_data.string_sanitizer';
}
