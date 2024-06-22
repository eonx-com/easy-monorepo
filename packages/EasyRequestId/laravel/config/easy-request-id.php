<?php
declare(strict_types=1);

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;

return [
    'http_headers' => [
        /**
         * Header used to resolve/send the correlation id from the HTTP request.
         */
        'correlation_id' => RequestIdInterface::DEFAULT_HTTP_HEADER_CORRELATION_ID,

        /**
         * Header used to resolve/send the request id from the HTTP request.
         */
        'request_id' => RequestIdInterface::DEFAULT_HTTP_HEADER_REQUEST_ID,
    ],

    /**
     * Enable bridge for eonx-com/easy-error-handler.
     */
    'easy_error_handler' => true,

    /**
     * Enable bridge for eonx-com/easy-logging.
     */
    'easy_logging' => true,

    /**
     * Enable bridge for eonx-com/easy-http-client.
     */
    'easy_http_client' => true,

    /**
     * Enable bridge for eonx-com/easy-webhook.
     */
    'easy_webhook' => true,
];
