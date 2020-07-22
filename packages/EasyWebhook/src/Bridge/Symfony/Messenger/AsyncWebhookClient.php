<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
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
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(
        MessageBusInterface $bus,
        WebhookClientInterface $client,
        WebhookResultStoreInterface $store
    ) {
        $this->bus = $bus;
        $this->client = $client;
        $this->store = $store;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        if ($webhook->isSendNow()) {
            return $this->client->sendWebhook($webhook);
        }

        $result = $this->store->store(new WebhookResult($webhook));

        $this->bus->dispatch(new SendWebhookMessage($webhook->getId()));

        return $result;
    }
}
