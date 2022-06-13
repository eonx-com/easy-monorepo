<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_BUGSNAG_HANDLED_EXCEPTIONS = 'easy_error_handler.param_bugsnag_handled_exceptions';

    /**
     * @var string
     */
    public const PARAM_BUGSNAG_IGNORED_EXCEPTIONS = 'easy_error_handler.param_bugsnag_ignored_exceptions';

    /**
     * @var string
     */
    public const PARAM_BUGSNAG_THRESHOLD = 'easy_error_handler.param_bugsnag_threshold';

    /**
     * @var string
     */
    public const PARAM_IGNORED_EXCEPTIONS = 'easy_error_handler.param_ignored_exceptions';

    /**
     * @var string
     */
    public const PARAM_IS_VERBOSE = 'easy_error_handler.param_is_verbose';

    /**
     * @var string
     */
    public const PARAM_LOGGER_EXCEPTION_LOG_LEVELS = 'easy_error_handler.param_logger_exception_log_levels';

    /**
     * @var string
     */
    public const PARAM_LOGGER_IGNORED_EXCEPTIONS = 'easy_error_handler.param_logger_ignored_exceptions';

    /**
     * @var string
     */
    public const PARAM_OVERRIDE_API_PLATFORM_LISTENER = 'easy_error_handler.param_override_api_platform_listener';

    /**
     * @var string
     */
    public const PARAM_RESPONSE_KEYS = 'easy_error_handler.param_response_keys';

    /**
     * @var string
     */
    public const PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_ENABLED = 'easy_error_handler.translate_internal_error_messages_enabled';

    /**
     * @var string
     */
    public const PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_LOCALE = 'easy_error_handler.translate_internal_error_messages_locale';

    /**
     * @var string
     */
    public const PARAM_TRANSLATION_DOMAIN = 'easy_error_handler.param_translation_domain';

    /**
     * @var string
     */
    public const TAG_ERROR_REPORTER_PROVIDER = 'easy_error_handler.error_reporter_provider';

    /**
     * @var string
     */
    public const TAG_ERROR_RESPONSE_BUILDER_PROVIDER = 'easy_error_handler.error_response_builder_provider';

    /**
     * @var string
     */
    public const TAG_VERBOSE_STRATEGY_DRIVER = 'easy_error_handler.verbose_strategy_driver';

    /**
     * @var string
     */
    public const TRANSLATION_NAMESPACE = 'easy-error-handler';
}
