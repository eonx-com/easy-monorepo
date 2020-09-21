---eonx_docs---
title: Configuration
weight: 2001
---eonx_docs---

### Create the configuration file

The package allows you to configure error response field names. 
Just copy `src/Bridge/Laravel/config/easy-error-handler.php` to `config/easy-error-handler.php` and adjust it to 
your needs (you can leave only the fields you want to override):

```php
# config/easy-error-handler.php

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
    'bugsnag_threshold' => \env('EASY_ERROR_HANDLER_EASY_BUGSNAG_ENABLED', null),

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
```

### Translations

If you want to update default package translations, copy the `src/Bridge/Laravel/translations/en/messages.php` 
to the `resources/lang/vendor/easy-error-handler/en/messages.php` and change it.
