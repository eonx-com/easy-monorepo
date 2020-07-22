<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\WebhookResult;
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
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(Dispatcher $bus, WebhookClientInterface $client, WebhookResultStoreInterface $store)
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

        $result = $this->store->store(new WebhookResult($webhook));

        $this->bus->dispatch(new SendWebhookJob($webhook->getId()));

        return $result;
    }
}
