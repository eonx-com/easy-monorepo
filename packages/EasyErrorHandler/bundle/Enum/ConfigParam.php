<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle\Enum;

enum ConfigParam: string
{
    case BugsnagHandledExceptions = 'easy_error_handler.bugsnag_handled_exceptions';

    case BugsnagIgnoredExceptions = 'easy_error_handler.bugsnag_ignored_exceptions';

    case BugsnagThreshold = 'easy_error_handler.bugsnag_threshold';

    case ErrorCodesCategorySize = 'easy_error_handler.error_codes_category_size';

    case ErrorCodesInterface = 'easy_error_handler.error_codes_interface';

    case IgnoredExceptions = 'easy_error_handler.ignored_exceptions';

    case IsVerbose = 'easy_error_handler.is_verbose';

    case LoggerExceptionLogLevels = 'easy_error_handler.logger_exception_log_levels';

    case LoggerIgnoredExceptions = 'easy_error_handler.logger_ignored_exceptions';

    case ReportRetryableExceptionAttempts = 'easy_error_handler.report_retryable_exception_attempts';

    case ResponseKeys = 'easy_error_handler.response_keys';

    case SkipReportedExceptions = 'easy_error_handler.skip_reported_exceptions';

    case TransformValidationErrors = 'easy_error_handler.transform_validation_errors';

    case TranslateInternalErrorMessagesEnabled = 'easy_error_handler.translate_internal_error_messages_enabled';

    case TranslateInternalErrorMessagesLocale = 'easy_error_handler.translate_internal_error_messages_locale';

    case TranslationDomain = 'easy_error_handler.translation_domain';
}
