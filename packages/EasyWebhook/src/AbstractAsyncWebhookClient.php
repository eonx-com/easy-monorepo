<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Exceptions\WebhookIdRequiredForAsyncException;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

abstract class AbstractAsyncWebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(
        WebhookClientInterface $client,
        WebhookResultStoreInterface $store
    ) {
        $this->client = $client;
        $this->store = $store;
    }

    public function configure(WebhookInterface $webhook): WebhookInterface
    {
        return $this->client->configure($webhook);
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        $webhook = $this->configure($webhook);
        
        if ($webhook->isSendNow()) {
            return $this->client->sendWebhook($webhook);
        }

        $result = $this->store->store(new WebhookResult($webhook));

        if ($webhook->getId() === null) {
            throw new WebhookIdRequiredForAsyncException(\sprintf('
                WebhookResult must be persisted and have a unique identifier before being sent asynchronously.
                Please verify your %s implementation sets this identifier and is registered as a service
            ', WebhookResultStoreInterface::class));
        }

        return $this->sendAsync($result);
    }

    abstract protected function sendAsync(WebhookResultInterface $result): WebhookResultInterface;
}
