<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string[]
     */
    public const MATH_PARAMS = [
        self::PARAM_MATH_FORMAT_DECIMAL_SEPARATOR,
        self::PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR,
        self::PARAM_MATH_ROUND_MODE,
        self::PARAM_MATH_ROUND_PRECISION,
        self::PARAM_MATH_SCALE,
    ];

    /**
     * @var string[]
     */
    public const SENSITIVE_DATA_PARAMS = [
        self::PARAM_SENSITIVE_DATA_KEYS_TO_MASK => 'keys_to_mask',
        self::PARAM_SENSITIVE_DATA_MASK_PATTERN => 'mask_pattern',
    ];

    /**
     * @var string
     */
    public const PARAM_MATH_FORMAT_DECIMAL_SEPARATOR = 'format_decimal_separator';

    /**
     * @var string
     */
    public const PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR = 'format_thousands_separator';

    /**
     * @var string
     */
    public const PARAM_MATH_ROUND_MODE = 'round_mode';

    /**
     * @var string
     */
    public const PARAM_MATH_ROUND_PRECISION = 'round_precision';

    /**
     * @var string
     */
    public const PARAM_MATH_SCALE = 'scale';

    /**
     * @var string
     */
    public const PARAM_SENSITIVE_DATA_KEYS_TO_MASK = 'easy_utils.keys_to_mask';

    /**
     * @var string
     */
    public const PARAM_SENSITIVE_DATA_MASK_PATTERN = 'easy_utils.mask_pattern';

    /**
     * @var string
     */
    public const TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER = 'easy_utils.object_transformer';

    /**
     * @var string
     */
    public const TAG_SENSITIVE_DATA_STRING_SANITIZER = 'easy_utils.string_sanitizer';
}
