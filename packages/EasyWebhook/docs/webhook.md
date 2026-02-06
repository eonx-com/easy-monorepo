---eonx_docs---
title: 'Webhook class'
weight: 1000
---eonx_docs---

# Webhook class

The **Webhook** class defines webhook configuration. Once configured, the [WebhookClient](webhook-client.md) class is
responsible for sending the webhook.

## Properties

A Webhook object has the following properties:

- `$allowRerun`: Boolean indicating whether a webhook can be rerun multiple times after the webhook is in a final state
  (i.e. success or failed).
- `$body`: Body of the webhook HTTP request, provided as an array of key-value pairs. The body will be formatted by the
  BodyFormatterMiddleware (which formats the body as JSON by default).
- `$bodyAsString`: Body of the webhook HTTP request, provided as a string. The body will be formatted by the
  BodyFormatterMiddleware (which formats the body as JSON by default). Note that `$bodyAsString` takes priority over
  `$body` if both are set.
- `$configured`: Boolean indicating whether a webhook is **configured**, meaning that some configure once middleware
  will not be processed for the webhook, e.g. the event header middleware. See [Middleware](middleware.md) for more
  information.
- `$currentAttempt`: Current attempt of the webhook.
- `$event`: Event descriptor, which will be sent in the Event header of the webhook HTTP request. The Event header is
  called `X-Webhook-Event` by default.
- `$extra`: Array of extra information pertaining to the webhook.
- `$httpClientOptions`: Array of HTTP client options for the webhook HTTP request.
- `$id`: A unique identifier for the webhook, which will be sent in the ID header of the webhook HTTP request. The ID
  header is called `X-Webhook-Id` by default.
- `$maxAttempt`: Maximum number of times to try to send the webhook before giving up.
- `$method`: HTTP method to use to send the webhook HTTP request, e.g. `PUT`. The HTTP method is `POST` by default.
- `$secret`: Secret key which will be used to construct a signature of the webhook HTTP request body that will be sent
  in the Signature header of the webhook HTTP request. The Signature header is called `X-Webhook-Signature` by default.
- `$sendAfter`: Timestamp after which a webhook may be sent. You can initiate sending of delayed webhooks via the
  console command `easy-webhooks:send-due-webhooks`.
- `$sendNow`: Boolean indicating that the webhook should be sent synchronously instead of asynchronously. Note that it
  is the responsibility of your application to retry webhooks if sending synchronously.
- `$status`: Status of the webhook, which may be one of `failed`, `failed_pending_retry`,`pending` or `success`. By
  default, the initial status is `pending`.
- `$url`: Target URL to send the webhook HTTP request.

## Methods

### Webhook creation methods

A simple `create()` method is provided by the class that allows you to easily create a webhook if all you need is a URL,
as well as an optional request body (provided as an array) and request method.

For example:

```php
// Create simple webhook with limited config
$webhook = Webhook::create('https://eonx.com/webhook', ['event' => 'showcase'], 'PUT');
```

For more complex situations, the `fromArray()` method is also available, allowing you to create a webhook with the
following parameters:

- `body`
- `body_as_string`
- `current_attempt`
- `event`
- `http_options`
- `id`
- `max_attempt`
- `method`
- `secret`
- `send_after`
- `status`
- `url`

For example:

```php
// Create webhook from array
$webhook = Webhook::fromArray([
    'url' => 'https://eonx.com/webhook',
    'body' => ['event' => 'showcase'],
    'method' => 'PUT',
    'max_attempt' => 5,
]);
```

The equivalent can also be done by using setters (see below for setter methods):

```php
// Create webhook using fluent setters
$webhook = (new Webhook())
    ->url('https://eonx.com/webhook')
    ->body(['event' => 'showcase'])
    ->method('PUT')
    ->maxAttempt(5);
```

Once a webhook has been created, use the `WebhookClient::sendWebhook()` method to trigger the processing of the webhook
and send the webhook HTTP request.

### Getters and setters

The following table summarises the getter and setter methods for a Webhook object's properties:

| Property             | Type     | Setter                | Getter                   |
|----------------------|----------|-----------------------|--------------------------|
| `$allowRerun`        | bool     | `allowRerun()`        | `isRerunAllowed()`       |
| `$body`              | array    | `body()`              | `getBody()`              |
| `$bodyAsString`      | string   | `bodyAsString()`      | `getBodyAsString()`      |
| `$configured`        | bool     | `configured()`        | `isConfigured()`         |
| `$currentAttempt`    | int      | `currentAttempt()`    | `getCurrentAttempt()`    |
| `$event`             | string   | `event()`             | `getEvent()`             |
| `$extra`             | array    | `extra()`             | `getExtra()`             |
| `$httpClientOptions` | array    | `httpClientOptions()` | `getHttpClientOptions()` |
| `$id`                | string   | `id()`                | `getId()`                |
| `$maxAttempt`        | int      | `maxAttempt()`        | `getMaxAttempt()`        |
| `$method`            | string   | `method()`            | `getMethod()`            |
| `$secret`            | string   | `secret()`            | `getSecret()`            |
| `$sendAfter`         | DateTime | `sendAfter()`         | `getSendAfter()`         |
| `$sendNow`           | bool     | `sendNow()`           | `isSendNow()`            |
| `$status`            | string   | `status()`            | `getStatus()`            |
| `$url`               | string   | `url()`               | `getUrl()`               |

The `$httpClientOptions` property can also be manipulated using the following methods:

- `header()`: Add HTTP request header to the HTTP client options.
- `headers()`: Set the array of HTTP request headers for the HTTP client options.
- `query()`: Add HTTP query parameter to the HTTP client options.
- `queries()`: Set the array of HTTP query parameters for the HTTP client options.
- `mergeHttpClientOptions()`: Merge array of HTTP client options into the existing HTTP client options.

The `$extra` property can also be manipulated with the `mergeExtra()` method, which merges an array of extra information
into the `$extra` property.
