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
];
