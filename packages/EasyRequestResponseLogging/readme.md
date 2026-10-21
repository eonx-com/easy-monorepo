---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

Logs every incoming HTTP request and its response (method, URI, query, headers, body, status code, duration,
request/correlation IDs) to a dedicated `easy_request_response_logging` log channel. All logged data is sanitized
with the EasyUtils sensitive data sanitizer. The feature is disabled by default.

It relies on the `kernel.terminate` event, so it works the same way with PHP-FPM, Swoole and serverless (Bref).

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-request-response-logging
```

### Integration into a project

The steps below show a Symfony project that uses EasyLogging `LoggerFactory` for its log channels.
All configuration options are listed in [Symfony install docs][2].

#### 1. Register bundles

`EasyRequestIdBundle` and `EasyUtilsBundle` are required. If one of them is not registered, the container build fails.

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyRequestId\Bundle\EasyRequestIdBundle::class => ['all' => true],
    EonX\EasyRequestResponseLogging\Bundle\EasyRequestResponseLoggingBundle::class => ['all' => true],
    EonX\EasyUtils\Bundle\EasyUtilsBundle::class => ['all' => true],
];
```

#### 2. Enable the sensitive data sanitizer

All logged data goes through the EasyUtils sensitive data sanitizer. The sanitizer is enabled by default. Keep it
enabled and add your application specific keys to `keys_to_mask`. These keys are merged with the default keys
(`EasyUtilsBundle::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK`), so do not repeat the default keys here.

```php
// config/packages/easy_utils.php

use Symfony\Config\EasyUtilsConfig;

return static function (EasyUtilsConfig $easyUtilsConfig): void {
    $easyUtilsConfig->sensitiveDataSanitizer()
        ->enabled(true)
        ->keysToMask([
            'paymentIntentSecret',
            'webhookApiKey',
            'x-custom-signature',
        ]);
};
```

#### 3. Enable request/response logging

The feature is disabled by default. Enable it in each project that needs it. Add the paths that must not be logged
(health checks, for example) to `do_not_log_paths`.

```php
// config/packages/easy_request_response_logging.php

use Symfony\Config\EasyRequestResponseLoggingConfig;

return static function (EasyRequestResponseLoggingConfig $easyRequestResponseLoggingConfig): void {
    $easyRequestResponseLoggingConfig
        ->enabled(true)
        ->doNotLogPaths(['/ping']);
};
```

To enable the feature only in some environments, put this file in `config/packages/<env>/`.

#### 4. Send the log channel to a handler

Records are logged at `info` level to the `easy_request_response_logging` channel. Make sure a handler accepts this
channel and this level, otherwise nothing is written.

With EasyLogging `LoggerFactory`, add the channel to a handler config provider:

```php
// src/Infrastructure/EasyLogging/ConfigProvider/ApplicationHandlerConfigProvider.php

use EonX\EasyLogging\Config\HandlerConfig;
use EonX\EasyLogging\Formatter\JsonFormatter;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam as EasyRequestResponseLoggingBundleParam;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

final class ApplicationHandlerConfigProvider implements HandlerConfigProviderInterface
{
    public function handlers(): iterable
    {
        $appHandler = new StreamHandler('php://stdout', LogLevel::DEBUG);
        $appHandler->setFormatter(new JsonFormatter());
        $appHandlerConfig = new HandlerConfig($appHandler);
        $appHandlerConfig->channels([
            'app',
            EasyRequestResponseLoggingBundleParam::LogChannel->value,
        ]);

        yield $appHandlerConfig;
    }
}
```

With `easy_logging.use_symfony_monolog_bundle` enabled, configure a MonologBundle handler instead:

```php
// config/packages/monolog.php

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monologConfig): void {
    $monologConfig->handler('request_response')
        ->type('stream')
        ->path('php://stdout')
        ->level('info')
        ->formatter('easy_logging.formatter.json')
        ->channels()
            ->elements(['easy_request_response_logging']);
};
```

#### 5. Check the result

Send a request to the application. One record is written for each request, with the `Request: "<METHOD> <path>" ->
<status code>` message and the `request`, `response`, `request_id` and `correlation_id` context keys. Make sure that
the sensitive values in headers, query and body are masked.

[1]: https://getcomposer.org/

[2]: docs/symfony/install.md
