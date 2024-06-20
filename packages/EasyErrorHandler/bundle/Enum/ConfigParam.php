<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle\Enum;

enum ConfigParam: string
{
    case BugsnagHandledExceptions = 'easy_error_handler.param_bugsnag_handled_exceptions';

    case BugsnagIgnoreValidationErrors = 'easy_error_handler.param_bugsnag_ignore_validation_errors';

    case BugsnagIgnoredExceptions = 'easy_error_handler.param_bugsnag_ignored_exceptions';

    case BugsnagThreshold = 'easy_error_handler.param_bugsnag_threshold';

    case ErrorCodesCategorySize = 'easy_error_handler.param_error_codes_category_size';

    case ErrorCodesInterface = 'easy_error_handler.param_error_codes_interface';

    case IgnoredExceptions = 'easy_error_handler.param_ignored_exceptions';

    case IsVerbose = 'easy_error_handler.param_is_verbose';

    case LoggerExceptionLogLevels = 'easy_error_handler.param_logger_exception_log_levels';

    case LoggerIgnoredExceptions = 'easy_error_handler.param_logger_ignored_exceptions';

    case OverrideApiPlatformListener = 'easy_error_handler.param_override_api_platform_listener';

    case ReportRetryableExceptionAttempts = 'easy_error_handler.param_report_retryable_exception_attempts';

    case ResponseKeys = 'easy_error_handler.param_response_keys';

    case SkipReportedExceptions = 'easy_error_handler.param_skip_reported_exceptions';

    case TransformValidationErrors = 'easy_error_handler.param_transform_validation_errors';

    case TranslateInternalErrorMessagesEnabled = 'easy_error_handler.translate_internal_error_messages_enabled';

    case TranslateInternalErrorMessagesLocale = 'easy_error_handler.translate_internal_error_messages_locale';

    case TranslationDomain = 'easy_error_handler.param_translation_domain';
}
