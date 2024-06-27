<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bundle\Enum;

enum ConfigParam: string
{
    case MathFormatDecimalSeparator = 'easy_utils.math.format_decimal_separator';

    case MathFormatThousandsSeparator = 'easy_utils.math.format_thousands_separator';

    case MathRoundMode = 'easy_utils.math.round_mode';

    case MathRoundPrecision = 'easy_utils.math.round_precision';

    case MathScale = 'easy_utils.math.scale';

    case SensitiveDataKeysToMask = 'easy_utils.sensitive_data.keys_to_mask';

    case SensitiveDataMaskPattern = 'easy_utils.sensitive_data.mask_pattern';

    case StringTrimmerExceptKeys = 'easy_utils.string_trimmer.except_keys';
}
