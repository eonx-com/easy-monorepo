<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Messenger\Dispatcher;

use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Messenger\Message\SendWebhookMessage;
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
