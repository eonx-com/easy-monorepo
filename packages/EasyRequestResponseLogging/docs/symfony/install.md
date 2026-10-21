---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyRequestResponseLogging\Bundle\EasyRequestResponseLoggingBundle::class => ['all' => true],
];
```

`EasyRequestIdBundle` and `EasyUtilsBundle` (with `sensitive_data_sanitizer` enabled) must be registered as well.

### Configuration

The feature is disabled by default and must be enabled explicitly:

```php
// config/packages/easy_request_response_logging.php

use Symfony\Config\EasyRequestResponseLoggingConfig;

return static function (EasyRequestResponseLoggingConfig $easyRequestResponseLoggingConfig): void {
    $easyRequestResponseLoggingConfig->enabled(true);

    // Defaults
    $easyRequestResponseLoggingConfig->doNotLogPaths(['/ping']);
    $easyRequestResponseLoggingConfig->logRequestBody(true);
    $easyRequestResponseLoggingConfig->logResponseBody(true);
    $easyRequestResponseLoggingConfig->maxBodySize(32768);
    $easyRequestResponseLoggingConfig->maxRawBodySize(1048576);
};
```

Records are logged at `info` level to the `easy_request_response_logging` channel, so make sure a handler accepts
this level on this channel.

Only bodies which can be sanitized by key are logged: `application/json`, any `+json` type and
`application/x-www-form-urlencoded`. Other bodies (XML, plain text, multipart, binary, streamed) are logged as `null`.
A body is sanitized first and then truncated to `max_body_size` bytes (`body_truncated: true`). Bodies bigger than
`max_raw_body_size` bytes, and bodies which cannot be sanitized, are not logged at all (`body: null`,
`body_omitted: true`).

The request path is sanitized as well, e.g. `/token/<value>` is logged as `/token/*REDACTED*`. `duration_ms` is the
time between the `kernel.request` and `kernel.response` events. It is `null` when a listener with a priority higher
than 1024 returned the response before the start time was recorded.

Headers, query and body are masked by the EasyUtils sensitive data sanitizer, which matches keys exactly
(case-insensitive). Its default keys already cover the `Authorization`, `Proxy-Authorization`, `Cookie`, `Set-Cookie`
and `X-Api-Key` headers. Add your application specific keys (e.g. `paymentIntentSecret`, custom signature headers) to
`easy_utils.sensitive_data_sanitizer.keys_to_mask`. If the sanitizer is disabled, the container build fails.

[1]: https://symfony.com/doc/current/setup/flex.html
