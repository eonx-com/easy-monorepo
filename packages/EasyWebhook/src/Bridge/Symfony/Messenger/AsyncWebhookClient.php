<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\AbstractAsyncWebhookClient;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncWebhookClient extends AbstractAsyncWebhookClient
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    public function __construct(
        MessageBusInterface $bus,
        WebhookClientInterface $client,
        WebhookResultStoreInterface $store
    ) {
        $this->bus = $bus;

        parent::__construct($client, $store);
    }

    protected function sendAsync(WebhookResultInterface $result): WebhookResultInterface
    {
        if ($result->getWebhook()->getId() !== null) {
            $this->bus->dispatch(new SendWebhookMessage($result->getWebhook()->getId()));
        }

        return $result;
    }
}
