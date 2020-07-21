<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use Illuminate\Contracts\Bus\Dispatcher;

final class AsyncWebhookClient implements WebhookClientInterface
{
    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
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

    public function __construct(Dispatcher $bus, WebhookClientInterface $client, WebhookStoreInterface $store)
    {
        $this->bus = $bus;
        $this->client = $client;
        $this->store = $store;
    }

    public function sendWebhook(WebhookInterface $webhook): void
    {
        if ($webhook->isSendNow()) {
            $this->client->sendWebhook($webhook);

            return;
        }

        $this->bus->dispatch(new SendWebhookJob($this->store->store($webhook->toArray(), $webhook->getId())));
    }
}
