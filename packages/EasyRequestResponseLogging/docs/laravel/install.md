---eonx_docs---
title: Laravel
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Register Service Providers

`EasyRequestIdServiceProvider` (with `EasyRandomServiceProvider`, which it depends on) and `EasyUtilsServiceProvider`
are required. If one of them is not registered when the feature is enabled, the application boot fails.

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyRandom\Laravel\EasyRandomServiceProvider::class,
    \EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider::class,
    \EonX\EasyRequestResponseLogging\Laravel\EasyRequestResponseLoggingServiceProvider::class,
    \EonX\EasyUtils\Laravel\EasyUtilsServiceProvider::class,
],
```

The service provider prepends the `RequestResponseLoggingMiddleware` global terminable middleware to the global
middleware, so the logged duration covers the whole pipeline.

`duration_ms` is the time between the request start (`REQUEST_TIME_FLOAT` server parameter) and the moment the
middleware gets the response, so it includes the application boot. The request start depends on the runtime: PHP-FPM
sets it when it receives the request, Bref when it converts the Lambda event (a cold start is not included),
OpenSwoole when it dispatches the request to a worker. It is `null` when the runtime does not set
`REQUEST_TIME_FLOAT`.

<br>

### Configuration

The feature is disabled by default. Enable it in each project that needs it.

```php
// config/easy-request-response-logging.php

return [
    /**
     * Enable request/response logging.
     */
    'enabled' => true,

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
```

All logged data goes through the EasyUtils sensitive data sanitizer. It matches keys regardless of case, separators
(space, `-` and `_`) and an `x` prefix, e.g. the `X-Api-Key` header and the `apiKey` field match the `api_key` key.
Keep `easy-utils.sensitive_data.enabled` set to `true` and add your application specific keys to
`easy-utils.sensitive_data.keys_to_mask`.

<br>

### Log channel

Records are logged at `info` level to the `easy_request_response_logging` channel:

- With `EasyLoggingServiceProvider` registered, the logger is created by the EasyLogging `LoggerFactory`. Add the
  `EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam::LogChannel` channel to a handler config provider.
- Without EasyLogging, the default `Psr\Log\LoggerInterface` of the application is used.
