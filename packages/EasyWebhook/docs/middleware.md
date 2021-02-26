---eonx_docs---
title: 'Middleware'
weight: 1003
---eonx_docs---

# Middleware

The EasyWebhook package uses a **middleware stack** that is processed every time a webhook is sent. Processing of the
middleware proceeds down the stack to where the webhook is sent in a HTTP request, and then proceeds back up the stack.
Actions may be taken on the way down and/or the way back up.

Middleware are services implementing `EonX\EasyWebhook\Interfaces\MiddlewareInterface`. You can write custom middleware,
e.g. to add a custom header to webhook HTTP requests.

The middleware is ordered by **priority** in the stack, whereby the lower the priority, the earlier in the stack the
middleware is placed.

The core middleware can have priorities between -5000 and 5000. If not specified, the default priority is 0. Some
middleware provided by this package must run before the core middleware, e.g. to lock the Webhook object, and some
middleware must run after the core middleware, e.g. to send the webhook HTTP request.

## Configure once middleware

Some middleware is **configure once** middleware. If the webhook is flagged as **configured**, i.e. its `$configured`
property is `true`, then configure once middleware will not operate on the webhook.

Note that if a webhook is retrieved from a webhook store, then it is automatically flagged as configured.

## Middleware list

The following middleware are provided by the EasyWebhook package:

### `AsyncMiddleware`

This middleware checks if the webhook should be sent *synchronously*. If so, this middleware proceeds with the remaining
middleware in the stack. If the webhook should be sent *asynchronously*, then this middleware stores the webhook and
offloads dispatching of the webhook to a framework-specific dispatcher.

Note that the dispatcher will retrieve the webhook from the store and trigger the processing of the middleware stack
again. The webhook is flagged as configured due to being retrieved from the store, so configure once middleware will not
operate on the webhook.

### `BodyFormatterMiddleware`

This middleware formats the body of the webhook HTTP request as a JSON string and sets the `Content-Type` header of the
HTTP request to be `application/json`.

*This is configure once middleware.*

### `EventHeaderMiddleware`

This middleware sets the `X-Webhook-Event` header of the webhook HTTP request to the webhook's `$event` property (if it
exists).

*This is configure once middleware.*

### `EventsMiddleware`

This middleware dispatches an event depending on the outcome of the webhook HTTP request. See see [Events](events.md))
for more information.

### `IdHeaderMiddleware`

This middleware sets the `X-Webhook-Id` header of the webhook HTTP request. It uses the webhook's `$id` property (if it
exists) or generates a new ID.

*This is configure once middleware.*

### `LockMiddleware`

This middleware locks the Webhook object at the start of middleware processing and unlocks it at the end.

### `MethodMiddleware`

This middleware sets the HTTP method for sending the webhook HTTP request.

*This is configure once middleware.*

### `RerunMiddleware`

If the webhook is in a final state (i.e. success or failed), this middleware checks if rerun is allowed for the webhook,
and if so, resets its `$currentAttempt` and its `$status` to `pending`, which allows the webhook to be rerun.

### `ResetStoreMiddleware`

This middleware resets the webhook store and webhook results store. See [Stores](stores.md) for more information.

Note that the stores must implement `EonX\EasyWebhook\Interfaces\Stores\ResetStoreInterface`. Of the stores provided by
the EasyWebhook package, only the array stores support reset.

### `SendAfterMiddleware`

This middleware checks the `$sendAfter` property of the webhook, and if it exists and is after the current date and
time, it simply stores the webhook in the configured store, and does not proceed with any further middleware processing.
You can initiate sending of delayed webhooks via the console command `easy-webhooks:send-due-webhooks`. See
[Console](console.md) for more information.

Note that the webhook store must implement `EonX\EasyWebhook\Interfaces\Stores\SendAfterStoreInterface` in order to be
able to find due webhooks. Of the stores provided by the EasyWebhook package, only the Doctrine DBAL webhook store
supports finding due webhooks.

### `SendWebhookMiddleware`

This middleware sends the webhook HTTP request and returns the result in a [WebhookResult](webhook-result.md) object.

### `SignatureHeaderMiddleware`

This middleware sets the `X-Webhook-Signature` header of the webhook HTTP request. The header contains a SHA-256 HMAC
(Hash-based Message Authentication Code) constructed by hashing the request body with either the webhook's `$secret`
property (if it exists) or the default secret.

*This is configure once middleware.*

### `StatusAndAttemptMiddleware`

This middleware updates the webhook status and current attempt after a response has been received from the webhook HTTP
request.

The current attempt is incremented.

The status can be set to one of:

- `success`: The webhook was successfully sent.
- `failed`: The webhook sending failed and the webhook has reached the maximum number of attempts allowed
- `failed_pending_retry`: The webhook sending failed but the webhook has not reached the maximum number of attempts
  allowed

### `StoreMiddleware`

This middleware stores the webhook and webhook result in the configured stores after a response has been received from
the webhook HTTP request. See [Stores](stores.md) for more information.

## Middleware stack

The following table show the middleware stack in priority order, with summaries of their actions:

| Middleware | Action forward :arrow_down: | Action back :arrow_up: |
| ---------- | --------------------------- | ---------------------- |
| `LockMiddleware` | Lock webhook | Unlock webhook |
| `RerunMiddleware` | If rerun allowed, reset status and current attempt<br/>If not allowed, throw exception | |
| *Begin core middleware*<br/>*(processed in priority order)* | | |
| `BodyFormatterMiddleware`| Format request body | |
| `EventHeaderMiddleware` | Set `X-Webhook-Event` request header | |
| `IdHeaderMiddleware` | Set `X-Webhook-Id` request header | |
| `SignatureHeaderMiddleware` | Set `X-Webhook-Signature` request header | |
| Custom middleware | Custom pre-processing | Custom post-processing |
| *End core middleware* | | |
| `ResetStoreMiddleware` | Reset webhook and result stores | |
| `MethodMiddleware` | Set request method | |
| `SendAfterMiddleware` | If time is before `$sendAfter`, store webhook and return up stack<br/>If time is after `$sendAfter`, continue down stack | |
| `AsyncMiddleware` | If asynchronous, store webhook and return up stack<br/>If synchronous, continue down stack | |
| `StoreMiddleware` | | Store webhook and result |
| `EventsMiddleware` | | Dispatch event |
| `StatusAndAttemptMiddleware` | | Update status and attempt |
| `SendWebhookMiddleware` | Send request and return up stack | |

::: warning FIXME
Priority of `SendAfterMiddleware`?
:::

## Custom middleware

Custom middleware must implement `EonX\EasyWebhook\Interfaces\MiddlewareInterface`. However, it may be easier to extend
one of the following classes:

- `EonX\EasyWebhook\Middleware\AbstractConfigureOnceMiddleware`: For middleware that should not be processed if the
  webhook is already "configured". Implement the `doProcess()` method.
- `EonX\EasyWebhook\Middleware\AbstractMiddleware`: For middleware that should be processed regardless of whether the
  webhook is "configured". Implement the `process()` method.

For example, here is how you would add a custom header to every webhook sent in configure once middleware:

```php
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class MyCustomHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        /* Add custom header */
        $webhook->header('X-My-Header', 'my-value');

        /* Send webhook down stack */
        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
```

This example shows placement of custom pre-processing and post-processing in normal middleware:

```php
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class MyCustomMiddleware extends AbstractMiddleware
{
    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        /* Add custom pre-processing */

        /* Send webhook down stack */
        $webhookResult = $stack
            ->next()
            ->process($webhook, $stack);

        /* Add custom post-processing */

        /* Return webhook result up stack */
        return $webhookResult;
    }
}
```
