<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum ConfigParam: string
{
    case BugsnagHandlerLevel = 'easy_logging.bugsnag_handler_level';

    case SensitiveDataSanitizerEnabled = 'easy_logging.sensitive_data_sanitizer_enabled';
}
