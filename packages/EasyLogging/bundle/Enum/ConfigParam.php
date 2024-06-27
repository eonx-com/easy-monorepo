<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum ConfigParam: string
{
    case BugsnagHandlerLevel = 'easy_logging.bugsnag_handler_level';

    case DefaultChannel = 'easy_logging.default_channel';

    case LazyLoggers = 'easy_logging.lazy_loggers';

    case LoggerClass = 'easy_logging.logger_class';

    case SensitiveDataSanitizerEnabled = 'easy_logging.sensitive_data_sanitizer_enabled';

    case StreamHandler = 'easy_logging.stream_handler';

    case StreamHandlerLevel = 'easy_logging.stream_handler_level';
}
