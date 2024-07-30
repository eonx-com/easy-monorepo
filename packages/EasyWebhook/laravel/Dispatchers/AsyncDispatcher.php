<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Laravel\Dispatchers;

use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Laravel\Jobs\SendWebhookJob;
use Illuminate\Contracts\Bus\Dispatcher;

final readonly class AsyncDispatcher implements AsyncDispatcherInterface
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
