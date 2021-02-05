<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Async;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class NullAsyncDispatcher implements AsyncDispatcherInterface
{
    public function dispatch(WebhookResultInterface $webhookResult): WebhookResultInterface
    {
        return $webhookResult;
    }
}
