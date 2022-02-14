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
}
