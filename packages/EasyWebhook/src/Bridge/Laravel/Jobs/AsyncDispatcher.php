<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use Illuminate\Contracts\Bus\Dispatcher;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {
    }

    public function dispatch(WebhookInterface $webhook): void
    {
        if ($webhook->getId() !== null) {
            $this->dispatcher->dispatch(new SendWebhookJob($webhook->getId(), $webhook->getMaxAttempt()));
        }
    }
}
