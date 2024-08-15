<?php
declare(strict_types=1);

return [
    'math' => [
        'format_decimal_separator' => null,
        'format_thousands_separator' => null,
        'round_mode' => null,
        'round_precision' => null,
        'scale' => null,
    ],
    'sensitive_data' => [
        'enabled' => \env('EASY_UTILS_SENSITIVE_DATA_ENABLED', true),
        'keys_to_mask' => [],
        'mask_pattern' => '*REDACTED*',
        'use_default_keys_to_mask' => true,
        'use_default_object_transformers' => true,
        'use_default_string_sanitizers' => true,
    ],
    'string_trimmer' => [
        'enabled' => \env('EASY_UTILS_STRING_TRIMMER_ENABLED', false),
        'except_keys' => [],
    ],
];
