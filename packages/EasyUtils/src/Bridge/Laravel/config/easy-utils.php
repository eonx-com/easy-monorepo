<?php

declare(strict_types=1);

return [
    'math' => [
        'round_precision' => null,
        'round_mode' => null,
        'scale' => null,
        'format_decimal_separator' => null,
        'format_thousands_separator' => null,
    ],
    'sensitive_data' => [
        'enabled' => true,
        'keys_to_mask' => [],
        'mask_pattern' => null,
        'use_default_keys_to_mask' => true,
        'use_default_object_transformers' => true,
        'use_default_string_sanitizers' => true,
    ],
    'string_trimmer' => [
        'enabled' => \env('EASY_UTILS_STRING_TRIMMER_ENABLED', false),
        'except_keys' => [],
    ],
];
