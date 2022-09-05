<?php

declare(strict_types=1);

return [
    /**
     * Use extended error response with exception message, trace, etc.
     */
    'use_extended_response' => \env('EASY_ERROR_HANDLER_USE_EXTENDED_RESPONSE', false),

    /**
     * Use default set of error response builders.
     */
    'use_default_builders' => \env('EASY_ERROR_HANDLER_USE_DEFAULT_BUILDERS', true),

    /**
     * Use default set of error reporters.
     */
    'use_default_reporters' => \env('EASY_ERROR_HANDLER_USE_DEFAULT_REPORTERS', true),

    /**
     * Register error reporter for easy-bugsnag automatically.
     */
    'bugsnag_enabled' => \env('EASY_ERROR_HANDLER_EASY_BUGSNAG_ENABLED', true),

    /**
     * Log level threshold to notify bugsnag.
     */
    'bugsnag_threshold' => \env('EASY_ERROR_HANDLER_EASY_BUGSNAG_THRESHOLD', null),

    /**
     * List of handled exceptions that will set the bugsnag report as handled.
     */
    'bugsnag_handled_exceptions' => null,

    /**
     * List of Ignored Exceptions that'll not be reported to bugsnag.
     */
    'bugsnag_ignored_exceptions' => null,

    /**
     * List of Ignored Exceptions that'll not be reported to any reporter.
     */
    'ignored_exceptions' => null,

    /**
     * List of Exceptions and their associated log levels.
     */
    'logger_exception_log_levels' => null,

    /**
     * List of Ignored Exceptions that'll not be reported to logger.
     */
    'logger_ignored_exceptions' => null,

    /**
     * Interface to fetch error codes from that will be used in the `easy-error-handler:error-codes:analyze` command.
     */
    'error_codes_interface' => null,

    /**
     * The number of error codes in error category.
     */
    'error_codes_category_size' => 100,

    /*
    |--------------------------------------------------------------------------
    | Error response
    |--------------------------------------------------------------------------
    |
    | Here you may customize error response field names
    |
    */
    'response' => [
        'code' => 'code',
        'exception' => 'exception',
        'extended_exception_keys' => [
            'class' => 'class',
            'file' => 'file',
            'line' => 'line',
            'message' => 'message',
            'trace' => 'trace',
        ],
        'message' => 'message',
        'sub_code' => 'sub_code',
        'time' => 'time',
        'violations' => 'violations',
    ],

    /**
     * Translate internal error messages.
     */
    'translate_internal_error_messages' => [
        'enabled' => \env('EASY_ERROR_HANDLER_TRANSLATE_INTERNAL_ERROR_MESSAGES_ENABLED', false),
        'locale' => \env('EASY_ERROR_HANDLER_TRANSLATE_INTERNAL_ERROR_MESSAGES_LOCALE', 'en'),
    ],
];
