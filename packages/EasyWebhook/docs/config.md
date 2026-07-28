---eonx_docs---
title: 'Configuration'
weight: 1009
---eonx_docs---

# Configuration

You can configure global settings for the EasyWebhook package via a configuration file in your application.

## Configuration files

For Laravel applications, the EasyWebhook configuration file must be called `easy-webhook.php` and be located in the
`config` directory.

For Symfony applications, the EasyWebhook configuration file can be a YAML, XML or PHP file located under the
`config/packages` directory, with a name like `easy_webhook.<format>`. The root node of the configuration must be called
`easy_webhook`.

## Configuration options

The common configuration options for Laravel and Symfony are as follows:

| Configuration            | Default               | Description                                                                                            |
|--------------------------|-----------------------|--------------------------------------------------------------------------------------------------------|
| `event.enabled`          | `true`                | Whether the EventHeaderMiddleware is enabled to send an Event header with webhook HTTP requests        |
| `event.header`           | `X-Webhook-Event`     | Name of the Event header                                                                               |
| `id.enabled`             | `true`                | Whether the IdHeaderMiddleware is enabled to send an ID header with webhook HTTP requests              |
| `id.header`              | `X-Webhook-Id`        | Name of the ID header                                                                                  |
| `method`                 | `POST`                | Method to use when sending webhook HTTP requests                                                       |
| `signature.enabled`      | `false`               | Whether the SignatureHeaderMiddleware is enabled to send a Signature header with webhook HTTP requests |
| `signature.header`       | `X-Webhook-Signature` | Name of the Signature header                                                                           |
| `signature.signer`       | `Rs256Signer:class`   | Class to use for signing the webhook HTTP request body                                                 |
| `signature.secret`       | N/A                   | Secret to use when signing the webhook HTTP request body                                               |
| `request_limits.timeout`            | `10`       | Idle timeout in seconds — abort when the target stops sending data. `0` keeps PHP's `default_socket_timeout` |
| `request_limits.max_duration`       | `30`       | Total request-duration cap in seconds, regardless of activity. `0` = unlimited                          |
| `request_limits.max_response_bytes` | `10485760` | Maximum response body size in bytes before the transfer is aborted. `0` = unlimited                     |
| `use_default_middleware` | `true`                | Whether to use the default middleware (currently, BodyFormatterMiddleware)                             |

Laravel has the following additional configuration option:

| Configuration | Default | Description                                           |
|---------------|---------|-------------------------------------------------------|
| `send_async`  | `true`  | Whether to send webhook HTTP requests asynchronously. |

Symfony has the following additional configuration options:

| Configuration   | Default                 | Description                                                  |
|-----------------|-------------------------|--------------------------------------------------------------|
| `async.enabled` | `true`                  | Whether to send webhook HTTP requests asynchronously.        |
| `async.bus`     | `messenger.bus.default` | Bus to use for asynchronously sending webhook HTTP requests. |

## Request limits (DoS protection)

Webhook target URLs are frequently supplied by external parties (for example,
tenant-registered subscription URLs). A malicious or broken target can otherwise tie up a
worker or exhaust resources on the sending side:

- **Slow / hanging responses** — without a total-duration cap, a target that trickles bytes can
  hold a worker (or PHP-FPM process) open indefinitely. `request_limits.max_duration` bounds the
  total time of each request.
- **Oversized / decompression-bomb responses** — the library reads the response body
  (`getContent()`) and, when a result store is configured, persists it. A huge body can exhaust
  memory and bloat storage. `request_limits.max_response_bytes` aborts the transfer as soon as
  the body exceeds the limit, before it is fully buffered (this also covers chunked responses
  with no `Content-Length`).

These limits are **enabled by default** with generous values, since a webhook response is
normally a small, fast acknowledgement. Set any option to `0` to disable that individual limit.
A request aborted by a limit surfaces as a **failed webhook** (and is retried by the configured
retry strategy), not as a crash.

> Note: enabling these by default is a behavioural change — a target that legitimately responds
> slowly (> `max_duration`) or returns a large body (> `max_response_bytes`) will now fail. Raise
> or disable the relevant limit if you have such a case.

## Example configuration files

### Symfony

In Symfony, you could have a configuration file called `easy_webhook.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Signers\Rs256Signer;
use Symfony\Config\EasyWebhookConfig;

return static function (EasyWebhookConfig $easyWebhookConfig): void {
    $easyWebhookConfig
        ->method('POST')
        ->useDefaultMiddleware(true);

    $easyWebhookConfig->async()
        ->enabled(true)
        ->bus('messenger.bus.custom');

    $easyWebhookConfig->event()
        ->enabled(true)
        ->header('My-Event-Header');

    $easyWebhookConfig->id()
        ->enabled(true)
        ->header('My-Id-Header');

    $easyWebhookConfig->signature()
        ->enabled(true)
        ->header('My-Signature-Header')
        ->signer(Rs256Signer::class)
        ->secret(env('APP_SECRET'));
};

```

### Laravel

In Laravel, the `easy-webhook.php` configuration file could look like the following:

``` php
<?php
declare(strict_types=1);

return [
    'event' => [
        'enabled' => true,
        'header' => 'My-Event-Header',
    ],

    'id' => [
        'enabled' => true,
        'header' => 'My-Id-Header',
    ],

    'method' => 'POST',

    'send_async' => true,

    'signature' => [
        'enabled' => true,
        'signer' => Rs256Signer::class,
        'header' => 'My-Signature-Header',
        'secret' => 'my-secret',
    ],

    'use_default_middleware' => true,
];
```
