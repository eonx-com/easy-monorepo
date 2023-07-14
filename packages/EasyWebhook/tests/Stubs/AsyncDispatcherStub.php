<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class AsyncDispatcherStub implements AsyncDispatcherInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    private array $dispatched = [];

    public function dispatch(WebhookInterface $webhook): void
    {
        $this->dispatched[] = $webhook;
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    public function getDispatchedWebhooks(): array
    {
        return $this->dispatched;
    }
}
