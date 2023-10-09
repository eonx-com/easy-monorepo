<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_BUGSNAG_HANDLED_EXCEPTIONS = 'easy_error_handler.param_bugsnag_handled_exceptions';

    public const PARAM_BUGSNAG_IGNORED_EXCEPTIONS = 'easy_error_handler.param_bugsnag_ignored_exceptions';

    public const PARAM_BUGSNAG_IGNORE_VALIDATION_ERRORS = 'easy_error_handler.param_bugsnag_ignore_validation_errors';

    public const PARAM_BUGSNAG_THRESHOLD = 'easy_error_handler.param_bugsnag_threshold';

    public const PARAM_ERROR_CODES_CATEGORY_SIZE = 'easy_error_handler.param_error_codes_category_size';

    public const PARAM_ERROR_CODES_INTERFACE = 'easy_error_handler.param_error_codes_interface';

    public const PARAM_IGNORED_EXCEPTIONS = 'easy_error_handler.param_ignored_exceptions';

    public const PARAM_IS_VERBOSE = 'easy_error_handler.param_is_verbose';

    public const PARAM_LOGGER_EXCEPTION_LOG_LEVELS = 'easy_error_handler.param_logger_exception_log_levels';

    public const PARAM_LOGGER_IGNORED_EXCEPTIONS = 'easy_error_handler.param_logger_ignored_exceptions';

    public const PARAM_OVERRIDE_API_PLATFORM_LISTENER = 'easy_error_handler.param_override_api_platform_listener';

    public const PARAM_REPORT_RETRYABLE_EXCEPTION_ATTEMPTS = 'easy_error_handler.param_report_retryable_exception_attempts';

    public const PARAM_RESPONSE_KEYS = 'easy_error_handler.param_response_keys';

    public const PARAM_TRANSFORM_VALIDATION_ERRORS = 'easy_error_handler.param_transform_validation_errors';

    public const PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_ENABLED = 'easy_error_handler.translate_internal_error_messages_enabled';

    public const PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_LOCALE = 'easy_error_handler.translate_internal_error_messages_locale';

    public const PARAM_TRANSLATION_DOMAIN = 'easy_error_handler.param_translation_domain';

    public const TAG_ERROR_REPORTER_PROVIDER = 'easy_error_handler.error_reporter_provider';

    public const TAG_ERROR_RESPONSE_BUILDER_PROVIDER = 'easy_error_handler.error_response_builder_provider';

    public const TAG_VERBOSE_STRATEGY_DRIVER = 'easy_error_handler.verbose_strategy_driver';

    public const TRANSLATION_NAMESPACE = 'easy-error-handler';
}
