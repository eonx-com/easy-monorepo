---eonx_docs---
title: Events
weight: 1
---eonx_docs---

### HttpRequestSentEvent

The HTTP client of this package (`WithEventsHttpClient`) dispatches
`EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent` once for each request, after the response is received or the
request fails.

| Method | Value |
| --- | --- |
| `getRequestData()` | Method, URL, request options and the time the request was sent |
| `getResponseData()` | Body, headers, status code and the time the response was received. `null` when the request failed |
| `getThrowable()` | The exception thrown by the request. `null` when a response was received |
| `getExtra()` | The array given in the `easy_http_client.request_data_extra` request option |

A response with an error status code (4xx, 5xx) is still a response: the event has the response data and no
exception.

### Listeners of the package

Two listeners record every request. Both are enabled by default.

| Listener | Config | What it records |
| --- | --- | --- |
| `LogHttpRequestSentListener` | `psr_logger` | `debug` log records: the request with all its options, the response with its body and headers, the exception class and message. In Symfony the records go to the `easy_http_client` channel |
| `HttpRequestSentBreadcrumbListener` | `easy_bugsnag` | A Bugsnag breadcrumb `HTTP Request Sent` with the same data. A breadcrumb is limited to 4096 bytes: when it is larger, the listener removes the response headers, then the request options, then the response body |

To turn a listener off in Symfony:

```php
// config/packages/easy_http_client.php

use Symfony\Config\EasyHttpClientConfig;

return static function (EasyHttpClientConfig $easyHttpClientConfig): void {
    $easyHttpClientConfig->psrLogger()
        ->enabled(false);

    $easyHttpClientConfig->easyBugsnag()
        ->enabled(false);
};
```

The `easy_bugsnag` listener needs `EasyBugsnagBundle`. Set `easy_bugsnag.enabled` to `false` when the application
does not register that bundle.

To turn a listener off in Laravel/Lumen:

```php
// config/easy-http-client.php

return [
    'easy_bugsnag' => [
        'enabled' => false,
    ],
    'psr_logger' => [
        'enabled' => false,
    ],
];
```

### Sensitive data

The listeners record the request options and the response body as they are. This includes the credentials and the
personal data that the application sends to the remote API or receives from it. The package masks only the requests
to AWS SES and SNS (see below). Everything else is masked by the sanitizers:

- Log records are masked when the sensitive data sanitizer of EasyLogging is enabled
  (`easy_logging.sensitive_data_sanitizer.enabled` in Symfony, `easy-logging.sensitive_data_sanitizer.enabled` in
  Laravel/Lumen).
- Breadcrumbs are masked when the sensitive data sanitizer of EasyBugsnag is enabled
  (`easy_bugsnag.sensitive_data_sanitizer.enabled` in Symfony, `easy-bugsnag.sensitive_data_sanitizer.enabled` in
  Laravel/Lumen).

Both sanitizers mask a value by the name of its key: the default keys of EasyUtils plus the configured keys
(`easy_utils.sensitive_data_sanitizer.keys_to_mask` in Symfony, `easy-utils.sensitive_data.keys_to_mask` in
Laravel/Lumen). A key matches only as a whole, for example `name` does not cover `accountName`. So:

- Add the names of the sensitive fields of each remote API to `keys_to_mask`. Check the list again when the API adds
  a field.
- A value that has no key of its own cannot be masked this way, for example the text of an e-mail or an SMS. For
  such requests turn the events off or change the recorded data, as described below.

### E-mails and SMS sent through AWS

`AwsMessagingRequestSanitizerListener` changes the recorded request and response of every request to AWS SES
(`email.<region>.amazonaws.com`) and SNS (`sns.<region>.amazonaws.com`), FIPS (`email-fips`, `sns-fips`) and China
(`.amazonaws.com.cn`) endpoints included, for example the requests of
`symfony/amazon-mailer` and `symfony/amazon-sns-notifier`. It runs before the listeners that record the request and
replaces with `*REDACTED*`:

- the `Authorization` and `X-Amz-Security-Token` headers
- in the body: the recipients (`ToAddresses`, `CcAddresses`, `BccAddresses`, `ReplyToAddresses`, `Destinations`), the
  subject (`Subject`), text and raw content of an e-mail (`Data`), the template data (`TemplateData`), the attachments
  (`RawContent`), the text of an SNS message (`Message`) and the phone number (`PhoneNumber`). A key also matches as a
  dot-separated part of a longer key, for example `PublishBatchRequestEntries.member.1.Message` in a form body (SNS,
  the SES v1 API). The field names of a form body are URL-decoded before the match
- in the response: the error message (`message` in a JSON body, `<Message>` in an XML body), as AWS puts the failed
  e-mail addresses and phone numbers there. The error code stays

The listener is always registered. The request itself is sent unchanged.

### Turn the events off for a request

Set the `easy_http_client.events_enabled` request option to `false`. The event is not dispatched for this request,
so there is no log record and no breadcrumb, and the listeners of the application are not called either.

```php
use EonX\EasyHttpClient\Common\Enum\HttpOption;

$response = $httpClient->request('POST', 'https://example.com/messages', [
    'json' => $payload,
    HttpOption::EventsEnabled->value => false,
]);
```

To turn the events off for every request to one remote API, wrap the client in a `ScopingHttpClient`:

```php
use EonX\EasyHttpClient\Common\Enum\HttpOption;
use Symfony\Component\HttpClient\ScopingHttpClient;

$messagesClient = ScopingHttpClient::forBaseUri($httpClient, 'https://example.com/', [
    HttpOption::EventsEnabled->value => false,
]);
```

Requests that this client sends to other URLs still dispatch the event.

Do not pass the option to `withOptions()`: it is read for each request only, and the decorated client rejects it as
an unsupported option.

### Change the recorded data

To keep the records but remove a part of the request, replace the request options in a listener that runs before the
listeners of the package. In Symfony these have the priority `0`:

```php
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(priority: 100)]
final class RemoveMessageTextListener
{
    public function __invoke(HttpRequestSentEvent $event): void
    {
        $requestData = $event->getRequestData();
        $options = $requestData->getOptions();

        if (isset($options['json']['text'])) {
            $options['json']['text'] = '*REDACTED*';
        }

        $requestData->setOptions($options);
    }
}
```

The request is already sent at this point, so the listener changes only what is recorded. To change the recorded
response body, call `setContent()` on `$event->getResponseData()`.
