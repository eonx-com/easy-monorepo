<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyWebhook\AbstractAsyncWebhookClient;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use Illuminate\Contracts\Bus\Dispatcher;

final class AsyncWebhookClient extends AbstractAsyncWebhookClient
{
    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    private $bus;

    public function __construct(Dispatcher $bus, WebhookClientInterface $client, WebhookResultStoreInterface $store)
    {
        $this->bus = $bus;

        parent::__construct($client, $store);
    }

    protected function sendAsync(WebhookResultInterface $result): WebhookResultInterface
    {
        $webhook = $result->getWebhook();

        if ($webhook->getId() !== null) {
            $this->bus->dispatch(new SendWebhookJob($webhook->getId(), $webhook->getMaxAttempt()));
        }

        return $result;
    }
}
