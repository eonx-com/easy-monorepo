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
| `ssrf_protection.enabled`        | `true`        | Whether outgoing webhook requests are blocked from reaching private/reserved IP ranges (SSRF protection) |
| `ssrf_protection.extra_blocked_ranges` | `[]`    | Additional CIDR ranges to reject **on top of** the standard private + reserved defaults (incl. link-local `169.254.0.0/16`) |
| `ssrf_protection.allowed_ranges` | `[]`          | CIDR ranges to unblock by **removing a matching entry** from the default block list (e.g. `127.0.0.0/8` for IPv4 localhost). Must match a default verbatim and not be covered by another default (e.g. `::1/128` is inside `::/96`), else rejected at startup |
| `request_limits.enabled`            | `false`    | Enable the DoS request limits below (opt-in; the default will flip to `true` in a future major) |
| `request_limits.timeout`            | `10`       | Idle timeout in seconds — abort when the target stops sending data. `0` keeps PHP's `default_socket_timeout` |
| `request_limits.max_duration`       | `30`       | Total request-duration cap in seconds, regardless of activity. `0` = unlimited                          |
| `request_limits.max_response_bytes` | `1048576`  | Maximum response body size in bytes before the transfer is aborted. `0` = unlimited                     |
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
  memory and bloat storage. `request_limits.max_response_bytes` counts the **decoded** body as it
  streams and aborts the transfer as soon as it exceeds the limit, before the body is fully
  buffered. Counting after decoding is what covers a compression bomb — a small gzipped payload
  that inflates to a huge body — as well as chunked responses that advertise no `Content-Length`.

These limits are **opt-in**: `request_limits.enabled` is `false` by default (the default will flip
to `true` in a future major). Enable them with `request_limits.enabled: true`; set any individual
option to `0` to disable just that limit.

A webhook's own http client options (`WebhookInterface::httpClientOptions()`) cannot weaken these
limits: `timeout` and `max_duration` are clamped so a per-webhook value may only make them stricter
(it can never raise the ceiling, and `0` cannot disable them), and `max_response_bytes` cannot be
overridden per request at all.

Retry behaviour differs by limit type, and neither is a crash:
- A **time**-limit abort (`timeout` / `max_duration`) is a failed webhook that **is retried** by the
  configured retry strategy — the target may simply have been slow.
- A **size**-limit abort (`max_response_bytes`) is a failed webhook that is **not retried** —
  retrying would only re-download the same oversized body.

## SSRF protection

Webhook URLs are frequently supplied by external parties (for example, tenant-registered
subscription URLs). Without egress control, an attacker can point a webhook at an internal
address — the cloud metadata endpoint (`169.254.169.254`), loopback, or another private host
— and have your server fetch it (Server-Side Request Forgery).

A URL-string check cannot catch DNS-rebinding (a public hostname that resolves to a private
IP) or a redirect to an internal target, so EasyWebhook enforces the block on the **resolved
IP of every request and every redirect hop**, using Symfony's `NoPrivateNetworkHttpClient`.

This protection is **enabled by default**. It exposes two narrowing knobs, plus an all-or-nothing
switch (`ssrf_protection.enabled: false`) to turn it off entirely. Both knobs build on the full
defaults, so the cloud-metadata endpoint and every IPv6 range stay covered, and the two can be
combined.

Use `ssrf_protection.extra_blocked_ranges` to reject ranges *on top of* the defaults. This exists
because the one thing the library cannot know is your project's own **publicly routable** internal
ranges: those are not private or reserved, so the built-in defaults do not cover them. For example,
if an internal-only admin service sits behind the public range `203.0.113.0/24` that no webhook
should ever reach, block it with `extra_blocked_ranges: ['203.0.113.0/24']`.

Use `ssrf_protection.allowed_ranges` to reach specific addresses by **removing a matching entry from
the default block list** — it is a list edit, not a general "allow this address". Each entry must be
one of the default ranges written verbatim; the usual case is reaching IPv4 localhost with
`allowed_ranges: ['127.0.0.0/8']`. Because it only removes the entry you name, it can unblock an
address only when no other default range also covers it — for example `::1/128` sits inside the
`::/96` default, so IPv6 loopback cannot be carved out this way. Anything that would have no effect
(an entry that is not a default, or one still covered by a broader default) is **rejected at
startup** rather than silently ignored. To reach hosts this cannot express, turn the protection off
with `ssrf_protection.enabled: false`.

> Note: enabling this by default is a behavioural change — webhooks whose URL resolves to a
> private or reserved IP are now rejected out of the box (surfaced as a failed webhook, not a
> crash). Set `ssrf_protection.enabled` to `false` to restore the previous behaviour.

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
