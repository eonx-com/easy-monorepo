---eonx_docs---
title: 'Middleware'
weight: 1003
---eonx_docs---

# Middleware

The EasyWebhook package uses a **middleware stack** that is processed every time a webhook is sent. Processing of the
middleware proceeds down the stack to where the webhook is sent in a HTTP request, and then proceeds back up the stack.
Actions may be taken on the way down and/or the way back up.

Middleware are services implementing `EonX\EasyWebhook\Common\Middleware\MiddlewareInterface`. This package provides **core
middleware**, but you can also write **custom middleware**, e.g. to add a custom header to webhook HTTP requests.

The middleware is ordered by **priority** in the stack, whereby the lower the priority, the earlier in the stack the
middleware is placed.

Custom middleware can have priorities between -5000 and 5000. If not specified, the default priority is 0. Some core
middleware provided by this package must run before the custom middleware, e.g. to lock the Webhook object, and some
core middleware must run after the core middleware, e.g. to send the webhook HTTP request.

## Configure once middleware

Some middleware are **configure once** middleware. If the webhook is flagged as **configured**, i.e. its `$configured`
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

This middleware formats the body of the webhook HTTP request.

By default, this package uses the `EonX\EasyWebhook\Formatters\JsonFormatter` body formatter, which formats the request
body as a JSON string and sets the `Content-Type` header of the HTTP request to be `application/json`.

You can use your own body formatter by setting the `EonX\EasyWebhook\Common\Formatter\WebhookBodyFormatterInterface` service
to your own implementation.

*This is configure once middleware.*

### `EventHeaderMiddleware`

This middleware sets the **Event header** of the webhook HTTP request to the webhook's `$event` property (if it exists).

The default name of the Event header is `X-Webhook-Event`, but the name is configurable (see
[Configuration](config.md)).

*This is configure once middleware.*

### `EventsMiddleware`

This middleware dispatches an event depending on the outcome of the webhook HTTP request. See see [Events](events.md))
for more information.

### `HandleExceptionsMiddleware`

This middleware catches exceptions that are thrown within the stack and handles them gracefully by returning a
failed WebhookResult containing the actual exception.

::: tip
To prevent an exception to be handled by this middleware, simply implement `EonX\EasyWebhook\Common\Exception\DoNotHandleMeEasyWebhookExceptionInterface`.
:::

### `IdHeaderMiddleware`

This middleware sets the **ID header** of the webhook HTTP request to the webhook's `$id` property (if it exists) or
generates a new ID.

The default name of the ID header is `X-Webhook-Id`, but the name is configurable (see [Configuration](config.md)).

*This is configure once middleware.*

### `LockMiddleware`

This middleware locks the Webhook object at the start of middleware processing and unlocks it at the end.

This middleware prevents the same webhook from being sent more than once if there are concurrency issues with multiple
workers consuming the asynchronous webhook queue.

### `MethodMiddleware`

This middleware sets the HTTP method for sending the webhook HTTP request to the webhook's `$method` property (if it
exists) or the method defined in the package configuration (see [Configuration](config.md)). The default method is
`POST`.

*This is configure once middleware.*

### `RerunMiddleware`

If the webhook is in a final state (i.e. success or failed), this middleware checks if rerun is allowed for the webhook,
and if so, resets its `$currentAttempt` and its `$status` to `pending`, which allows the webhook to be rerun.

This middleware ensures that webhooks will not be rerun after reaching a final state unless explicitly allowed by the
webhook.

### `ResetStoreMiddleware`

This middleware resets the webhook store and webhook results store. See [Stores](stores.md) for more information.

This middleware prevents memory issues when sending webhooks asynchronously with stores that use memory for their
storage, such as the array stores.

Note that the stores must implement `EonX\EasyWebhook\Common\Store\ResetStoreInterface`. Of the stores provided by
the EasyWebhook package, only the array stores support reset.

### `SendAfterMiddleware`

This middleware checks the `$sendAfter` property of the webhook, and if it exists and is after the current date and
time, it simply stores the webhook in the configured store, and does not proceed with any further middleware processing.
You can initiate sending of delayed webhooks via the console command `easy-webhooks:send-due-webhooks`. See
[Console](console.md) for more information.

Note that the webhook store must implement `EonX\EasyWebhook\Common\Store\SendAfterStoreInterface` in order to be
able to find due webhooks. Of the stores provided by the EasyWebhook package, only the Doctrine DBAL webhook store
supports finding due webhooks.

### `SendWebhookMiddleware`

This middleware sends the webhook HTTP request and returns the result in a [WebhookResult](webhook-result.md) object.

### `SignatureHeaderMiddleware`

This middleware sets the **Signature header** of the webhook HTTP request.

By default, this package uses the `EonX\EasyWebhook\Signers\Rs256Signer` signer. The header will contain a SHA-256 HMAC
(Hash-based Message Authentication Code) constructed by hashing the webhook's request body with either the webhook's
`$secret` property (if it exists) or the secret defined in the package configuration (see [Configuration](config.md)).

You can use your own signer by setting the `EonX\EasyWebhook\Common\Signer\WebhookSignerInterface` service to your own
implementation.

The default name of the Signature header is `X-Webhook-Signature`, but the name is configurable (see
[Configuration](config.md)).

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

### `SyncRetryMiddleware`

If the webhook was sent synchronously, and it failed, this middleware retries sending the webhook.
It provides a simple out-of-the-box solution for handling retries. However, we strongly recommend sending webhooks
asynchronously, so your application is not blocked by retries.

## Middleware stack

The following table show the middleware stack in priority order, with summaries of their actions:

| Middleware                                                    | Action forward :arrow_down:                                                                                              | Action back :arrow_up:                   |
|---------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------|------------------------------------------|
| *Begin initial core middleware*                               |                                                                                                                          |                                          |
| `LockMiddleware`                                              | Lock webhook                                                                                                             | Unlock webhook                           |
| `StoreMiddleware`                                             |                                                                                                                          | Store webhook and result                 |
| `StatusAndAttemptMiddleware`                                  |                                                                                                                          | Update status and attempt                |
| `HandleExceptionsMiddleware`                                  |                                                                                                                          | Handle exception thrown within the stack |
| `ResetStoreMiddleware`                                        | Reset webhook and result stores                                                                                          |                                          |
| `EventsMiddleware`                                            |                                                                                                                          | Dispatch event                           |
| `RerunMiddleware`                                             | If rerun allowed, reset status and current attempt<br/>If not allowed, throw exception                                   |                                          |
| *End initial core middleware*                                 |                                                                                                                          |                                          |
| *Begin custom middleware*<br/>*(processed in priority order)* |                                                                                                                          |                                          |
| `BodyFormatterMiddleware`                                     | Format request body                                                                                                      |                                          |
| `EventHeaderMiddleware`                                       | Set Event request header                                                                                                 |                                          |
| `IdHeaderMiddleware`                                          | Set ID request header                                                                                                    |                                          |
| `SignatureHeaderMiddleware`                                   | Set Signature request header                                                                                             |                                          |
| Custom middleware                                             | Custom pre-processing                                                                                                    | Custom post-processing                   |
| *End custom middleware*                                       |                                                                                                                          |                                          |
| *Begin final core middleware*                                 |                                                                                                                          |                                          |
| `MethodMiddleware`                                            | Set request method                                                                                                       |                                          |
| `SendAfterMiddleware`                                         | If time is before `$sendAfter`, store webhook and return up stack<br/>If time is after `$sendAfter`, continue down stack |                                          |
| `AsyncMiddleware`                                             | If asynchronous, store webhook and return up stack<br/>If synchronous, continue down stack                               |                                          |
| `SyncRetryMiddleware`                                         | If asynchronous, continue down stack<br/>If synchronous, retries webhook if not successful                               |                                          |
| `SendWebhookMiddleware`                                       | Send request and return up stack                                                                                         |                                          |
| *End final core middleware*                                   |                                                                                                                          |                                          |

## Custom middleware

Custom middleware must implement `EonX\EasyWebhook\Common\Middleware\MiddlewareInterface`. However, it may be easier to extend
one of the following classes:

- `EonX\EasyWebhook\Common\Middleware\AbstractConfigureOnceMiddleware`: For middleware that should not be processed if the
  webhook is already "configured". Implement the `doProcess()` method.
- `EonX\EasyWebhook\Common\Middleware\AbstractMiddleware`: For middleware that should be processed regardless of whether the
  webhook is "configured". Implement the `process()` method.

For example, here is how you would add a custom header to every webhook sent in configure once middleware:

```php
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
