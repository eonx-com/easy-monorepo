<?php
declare(strict_types=1);

return [
    /**
     * Define the default channel name for the application.
     */
    'default_channel' => 'app',

    /**
     * Identify channels for lazy creation. "*" means all.
     */
    'lazy_loggers' => [],

    /**
     * Enable/Disable the override of the default logger.
     */
    'override_default_logger' => true,

    'sensitive_data_sanitizer' => [
        /**
         * Enable/Disable sensitive data sanitization.
         */
        'enabled' => false,
    ],

    /**
     * Enable/Disable the default stream handler.
     */
    'stream_handler' => true,

    /**
     * The log level to set on the default stream handler, defaults to DEBUG.
     */
    'stream_handler_level' => null,
];
