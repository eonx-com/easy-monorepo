---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

[ ] Refactor bridges
[ ] Refactor tests

Send webhooks asynchronously, retry if it fails, and persist them into the store of your choice, all that out of the box!

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-webhook
```

<br>

### Usage

The webhook client to send webhooks is automatically registered within the service container, so you can use
dependency injection to access the fully configured client easily.

```php
// MyService.php

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Webhook;

final class MyService
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $webhookClient;

    public function __construct(WebhookClientInterface $webhookClient)
    {
        $this->webhookClient = $webhookClient;
    }

    public function send(): void
    {
        // Create simple webhook with limited config
        $webhook = Webhook::create('https://eonx.com/webhook', ['event' => 'showcase'], 'PUT');

        // Create webhook from array
        $webhook = Webhook::fromArray([
            'url' => 'https://eonx.com/webhook',
            'body' => ['event' => 'showcase'],
            'method' => 'PUT',
            'max_attempt' => 5,
        ]);

        // Create webhook using fluent setters
        $webhook = (new Webhook())
            ->url('https://eonx.com/webhook')
            ->body(['event' => 'showcase'])
            ->method('PUT')
            ->maxAttempt(5);

        // Send the webhook
        $this->webhookClient->sendWebhook($webhook);
    }
}
```

If not set the default method of a webhook is `POST`.

<br>

### Send webhooks synchronously

By default, this package will send webhooks asynchronously if possible. This logic can be changed for every webhook
at the configuration level by setting `send_async = false`. It can also be changed for specific webhook using the
`sendNow()` method.

```php
$webhookClient->sendWebhook(Webhook::create('https://eonx.com')->sendNow());
```

<br>

### Retry Strategy

By default, this package uses a retry-strategy which will increase the delay between retries exponentially. To modify
the retry-strategy simply override the `EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface` service with your
own implementation, or the default one with different arguments.

<br>

### Webhook Configurators

This package has concept of webhook configurators which are used every time a webhook is sent. This mechanism is used
internally to format the request body as an example. Configurators can also be used by the applications for manipulating
every webhooks before sending them.

A webhook configurator is a service implementing `EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface`.

<p style="display: none;"></p>

::: tip | Tip
Depends on the framework used by the application, the configurators might be automatically injected in the webhook
client or you might have to manually tag them when registering them.
:::

The configurators are executed according to their priority, the higher the priority is and the later the configurator
will be executed. The default configurators of this package have a priority of `5000`.

Here is how you would add a custom header to every webhook sent:

```php
use EonX\EasyWebhook\Configurators\AbstractWebhookConfigurator;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

/**
 * Extend the AbstractWebhookConfigurator so you don't have to worry about priority.
 */
final class MyCustomHeaderWebhookConfigurator extends AbstractWebhookConfigurator
{
    public function configure(WebhookInterface $webhook): void
    {
        $webhook->mergeHttpClientOptions([
            'headers' => [
                'X-My-Header' => 'my-value',
            ],
        ]);
    }
}
```

<br>

### Webhook Result store

This package allows you to store webhook results within the persisting layer of your choice. By default, it will not
store them anywhere.

To change the storing logic, simply override the `EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface` service
with you own.

<p style="display: none;"></p>

::: tip | Tip
This package comes with a Doctrine DBAL store implementation you can use by simply providing it with your own connection.
This store will persist each extra information on the webhook as a separate column.
:::

<br>

### Events

Events containing the webhook result are dispatched so you can have business logic associated to it:

- `EonX\EasyWebhook\Events\SuccessWebhookEvent`: dispatched when webhook sent successfully
- `EonX\EasyWebhook\Events\FailedWebhookEvent`: dispatched when webhook failed and is waiting to be retried
- `EonX\EasyWebhook\Events\FinalFailedWebhookEvent`: dispatched when webhook failed and cannot be retried anymore

[1]: https://getcomposer.org/
