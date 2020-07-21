<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhook\WebhookResult;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncWebhookClient implements WebhookClientInterface
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookStoreInterface
     */
    private $store;

    public function __construct(MessageBusInterface $bus, WebhookClientInterface $client, WebhookStoreInterface $store)
    {
        $this->bus = $bus;
        $this->client = $client;
        $this->store = $store;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        if ($webhook->isSendNow()) {
            return $this->client->sendWebhook($webhook);
        }

        $webhook->setId($this->store->store($webhook->toArray(), $webhook->getId()));

        $this->bus->dispatch(new SendWebhookMessage($webhook->getId()));

        return new WebhookResult($webhook);
    }
}
