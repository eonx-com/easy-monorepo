<?php
declare(strict_types=1);

return [
    'http_headers' => [
        /**
         * Header used to resolve/send the correlation id from the HTTP request.
         */
        'correlation_id' => 'X-CORRELATION-ID',

        /**
         * Header used to resolve/send the request id from the HTTP request.
         */
        'request_id' => 'X-REQUEST-ID',
    ],

    /**
     * Enable integration with eonx-com/easy-error-handler.
     */
    'easy_error_handler' => true,

    /**
     * Enable integration with eonx-com/easy-logging.
     */
    'easy_logging' => true,

    /**
     * Enable integration with eonx-com/easy-http-client.
     */
    'easy_http_client' => true,

    /**
     * Enable integration with eonx-com/easy-webhook.
     */
    'easy_webhook' => true,
];
