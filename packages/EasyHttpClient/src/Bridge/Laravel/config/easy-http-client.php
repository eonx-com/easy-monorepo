<?php

declare(strict_types=1);

return [
    /**
     * Automatically decorate the http client used by eonx-com/easy-webhook.
     */
    'decorate_easy_webhook_client' => false,

    /**
     * Enables listener to add Bugsnag breadcrumbs on each HTTP request.
     */
    'easy_bugsnag_enabled' => true,

    'modifiers' => [
        /**
         * Enables request_data_modifiers globally, null defaults to true.
         */
        'enabled' => null,

        /**
         * List of request_data_modifiers allowed to be executed, null allows any modifier.
         */
        'whitelist' => null,
    ],

    /**
     * Enables listener to log messages on each HTTP request.
     */
    'psr_logger_enabled' => true,
];
