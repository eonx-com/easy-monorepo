<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function dispatch(WebhookInterface $webhook): void
    {
        $webhookId = $webhook->getId();

        if ($webhookId !== null) {
            $this->bus->dispatch(new SendWebhookMessage($webhookId));
        }
    }
}
