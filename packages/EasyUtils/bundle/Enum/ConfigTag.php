<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bundle\Enum;

enum ConfigTag: string
{
    case SensitiveDataObjectTransformer = 'easy_utils.sensitive_data.object_transformer';

    case SensitiveDataStringSanitizer = 'easy_utils.sensitive_data.string_sanitizer';
}
