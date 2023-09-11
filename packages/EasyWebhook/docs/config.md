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

| Configuration                | Default               | Description                                                                                            |
|------------------------------|-----------------------|--------------------------------------------------------------------------------------------------------|
| `event.enabled`              | `true`                | Whether the EventHeaderMiddleware is enabled to send an Event header with webhook HTTP requests        |
| `event.event_header`         | `X-Webhook-Event`     | Name of the Event header                                                                               |
| `id.enabled`                 | `true`                | Whether the IdHeaderMiddleware is enabled to send an ID header with webhook HTTP requests              |
| `id.id_header`               | `X-Webhook-Id`        | Name of the ID header                                                                                  |
| `method`                     | `POST`                | Method to use when sending webhook HTTP requests                                                       |
| `signature.enabled`          | `false`               | Whether the SignatureHeaderMiddleware is enabled to send a Signature header with webhook HTTP requests |
| `signature.signature_header` | `X-Webhook-Signature` | Name of the Signature header                                                                           |
| `signature.signer`           | `Rs256Signer:class`   | Class to use for signing the webhook HTTP request body                                                 |
| `signature.secret`           | N/A                   | Secret to use when signing the webhook HTTP request body                                               |
| `use_default_middleware`     | `true`                | Whether to use the default middleware (currently, BodyFormatterMiddleware)                             |

Laravel has the following additional configuration option:

| Configuration | Default | Description                                           |
|---------------|---------|-------------------------------------------------------|
| `send_async`  | `true`  | Whether to send webhook HTTP requests asynchronously. |

Symfony has the following additional configuration options:

| Configuration   | Default                 | Description                                                  |
|-----------------|-------------------------|--------------------------------------------------------------|
| `async.enabled` | `true`                  | Whether to send webhook HTTP requests asynchronously.        |
| `async.bus`     | `messenger.bus.default` | Bus to use for asynchronously sending webhook HTTP requests. |

## Example configuration files

### Symfony

In Symfony, you could have a configuration file called `easy_webhook.yaml` that looks like the following:

``` yaml
easy_webhook:
    async:
        enabled: true
        bus: 'messenger.bus.custom'
    event:
        enabled: true
        event_header: 'My-Event-Header'
    id:
        enabled: true
        id_header: 'My-Id-Header'
    method: 'POST'
    signature:
        enabled: true
        signature_header: 'My-Signature-Header'
        signer: 'Rs256Signer::class'
        secret: 'my-secret'
    use_default_middleware: true
```

### Laravel

In Laravel, the `easy-webhook.php` configuration file could look like the following:

``` php
<?php
declare(strict_types=1);

return [
    'event' => [
        'enabled' => true,
        'event_header' => 'My-Event-Header',
    ],

    'id' => [
        'enabled' => true,
        'id_header' => 'My-Id-Header',
    ],

    'method' => 'POST',

    'send_async' => true,

    'signature' => [
        'enabled' => true,
        'signer' => Rs256Signer::class,
        'signature_header' => 'My-Signature-Header',
        'secret' => 'my-secret',
    ],

    'use_default_middleware' => true,
];
```
