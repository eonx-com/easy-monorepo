<?php

declare(strict_types=1);

use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;

return [
    /**
     * Key to use when integrating correlation-id in bridges.
     */
    'correlation_id_key' => RequestIdKeysAwareInterface::KEY_CORRELATION_ID,

    /**
     * Enable default resolver.
     */
    'default_resolver' => true,

    /**
     * Header used by default resolver to resolve correlation-id.
     */
    'default_correlation_id_header' => RequestIdKeysAwareInterface::KEY_CORRELATION_ID,

    /**
     * Header used by default resolver to resolve request-id.
     */
    'default_request_id_header' => RequestIdKeysAwareInterface::KEY_REQUEST_ID,

    /**
     * Enable bridge for eonx-com/easy-bugsnag.
     */
    'easy_bugsnag' => true,

    /**
     * Enable bridge for eonx-com/easy-error-handler.
     */
    'easy_error_handler' => true,

    /**
     * Enable bridge for eonx-com/easy-logging.
     */
    'easy_logging' => true,

    /**
     * Enable bridge for eonx-com/easy-webhook.
     */
    'easy_webhook' => true,

    /**
     * Key to use when integrating request-id in bridges.
     */
    'request_id_key' => RequestIdKeysAwareInterface::KEY_REQUEST_ID,
];
