---eonx_docs---
title: 'WebhookClient class'
weight: 1001
---eonx_docs---

# WebhookClient class

The **WebhookClient** class triggers the processing of the [middleware](middleware.md) stack for a webhook via the
`sendWebhook()` method.

For example:

```php
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;use EonX\EasyWebhook\Common\Entity\Webhook;

final class MyService
{
    /**
     * @var \EonX\EasyWebhook\Common\Client\WebhookClientInterface
     */
    private $webhookClient;

    public function __construct(WebhookClientInterface $webhookClient)
    {
        $this->webhookClient = $webhookClient;
    }

    public function send(): void
    {
        // Create simple webhook
        $webhook = Webhook::create('https://eonx.com/webhook', ['event' => 'showcase'], 'PUT');

        // Send the webhook
        $this->webhookClient->sendWebhook($webhook);
    }
}
```
