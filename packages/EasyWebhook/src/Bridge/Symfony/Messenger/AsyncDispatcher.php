<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(WebhookResultInterface $webhookResult): WebhookResultInterface
    {
        $webhookId = $webhookResult->getWebhook()
            ->getId();

        if ($webhookId !== null) {
            $this->bus->dispatch(new SendWebhookMessage($webhookId));
        }

        return $webhookResult;
    }
}
