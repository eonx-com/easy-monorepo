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

`EasyRequestIdBundle` (with `EasyRandomBundle`, which it depends on) and `EasyUtilsBundle` (with
`sensitive_data_sanitizer` enabled) must be registered as well.

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
`body_omitted: true`). A body with a `Content-Length` header bigger than `max_raw_body_size` is not even read, and
a request body without it (e.g. chunked) is read only up to `max_raw_body_size` bytes.
A query parameter, header, route parameter or request/correlation ID which cannot be sanitized is logged as `null`
(as `[omitted]` in the path). Unsanitized data is never logged.

The request path is logged decoded and sanitized using the matched route: its parameters are sanitized by name and by
value, logged as `request.route_params` (with the route name as `request.route`), and every masked parameter value is
masked in the path too. E.g. with the `/token/{token}` route, `/token/<value>` is logged as `/token/*REDACTED*`.
Name the route parameters which carry secrets after a key from `keys_to_mask`, or add their names to it. A path
without a matched route has no known structure, so only the sanitizer itself applies to it, and it is logged as
`[omitted]` when it contains a character which could end a masked value and expose its rest: whitespace,
`& ? # , ; " ' ) ] } < >` (also when percent-encoded) or an encoded `/` (`%2F`).

`duration_ms` is the time between the request start (`REQUEST_TIME_FLOAT` server parameter) and the `kernel.response`
event, so it includes the kernel boot and all `kernel.request` listeners. The request start depends on the runtime:
PHP-FPM sets it when it receives the request, Bref when it converts the Lambda event (a cold start is not included),
OpenSwoole when it dispatches the request to a worker. It is `null` when the runtime does not set `REQUEST_TIME_FLOAT`.

Headers, query and body are masked by the EasyUtils sensitive data sanitizer. It matches keys regardless of case,
separators (space, `-` and `_`) and an `x` prefix, e.g. the `X-Api-Key` header and the `apiKey` field match the
`api_key` key. Its default keys already cover the `Authorization`, `Proxy-Authorization`, `Cookie`, `Set-Cookie`
and `X-Api-Key` headers. Add your application specific keys (e.g. `paymentIntentSecret`, custom signature headers) to
`easy_utils.sensitive_data_sanitizer.keys_to_mask`. If the sanitizer is disabled, the container build fails.

[1]: https://symfony.com/doc/current/setup/flex.html
