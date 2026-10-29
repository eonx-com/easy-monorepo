<?php
declare(strict_types=1);

return [
    /**
     * Enable request/response logging.
     */
    'enabled' => false,

    /**
     * Request paths (exact match with the path info) which are not logged.
     */
    'do_not_log_paths' => ['/ping'],

    /**
     * Log the request body (only text content types are logged).
     */
    'log_request_body' => true,

    /**
     * Log the response body (only text content types are logged).
     */
    'log_response_body' => true,

    /**
     * Max size in bytes of the logged body, a bigger sanitized body is truncated.
     */
    'max_body_size' => 32768,

    /**
     * Max size in bytes of the raw body, a bigger body is not logged at all.
     */
    'max_raw_body_size' => 1048576,
];
