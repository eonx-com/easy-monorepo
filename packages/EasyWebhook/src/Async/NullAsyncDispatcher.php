<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Async;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class NullAsyncDispatcher implements AsyncDispatcherInterface
{
    public function dispatch(WebhookInterface $webhook): void
    {
        // No body needed
    }
}
