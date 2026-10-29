---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Register Service Providers

`EasyRequestIdServiceProvider` (with `EasyRandomServiceProvider`, which it depends on) and `EasyUtilsServiceProvider`
are required. If one of them is not registered when the feature is enabled, the application boot fails.

In a Laravel application:

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

In a Lumen application:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyRandom\Laravel\EasyRandomServiceProvider::class);
$app->register(\EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider::class);
$app->register(\EonX\EasyRequestResponseLogging\Laravel\EasyRequestResponseLoggingServiceProvider::class);
$app->register(\EonX\EasyUtils\Laravel\EasyUtilsServiceProvider::class);
```

The service provider registers the `RequestResponseLoggingMiddleware` global terminable middleware:

- Laravel: the middleware is prepended to the global middleware, so the logged duration covers the whole pipeline.
- Lumen: the middleware is appended to the global middleware. Register `EasyRequestResponseLoggingServiceProvider`
  before the other service providers which register global middleware, to include them in the logged duration.

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

In a Lumen application, load the config file in `bootstrap/app.php`:

```php
$app->configure('easy-request-response-logging');
```

All logged data goes through the EasyUtils sensitive data sanitizer. Keep `easy-utils.sensitive_data.enabled` set to
`true` and add your application specific keys to `easy-utils.sensitive_data.keys_to_mask`.

<br>

### Log channel

Records are logged at `info` level to the `easy_request_response_logging` channel:

- With `EasyLoggingServiceProvider` registered, the logger is created by the EasyLogging `LoggerFactory`. Add the
  `EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam::LogChannel` channel to a handler config provider.
- Without EasyLogging, the default `Psr\Log\LoggerInterface` of the application is used.

<br>

### Lumen with `Application::handle()`

Lumen calls terminable middleware inside `Application::handle()`, before the response is returned. When the
application is run with `handle()` instead of `run()` (for example, with Bref and EasyServerless), the logging is done
before the response is sent to the client.
