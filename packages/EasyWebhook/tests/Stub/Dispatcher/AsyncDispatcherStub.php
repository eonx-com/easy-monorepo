<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Dispatcher;

use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;

final class AsyncDispatcherStub implements AsyncDispatcherInterface
{
    /**
     * @var \EonX\EasyWebhook\Common\Entity\WebhookInterface[]
     */
    private array $dispatched = [];

    public function dispatch(WebhookInterface $webhook): void
    {
        $this->dispatched[] = $webhook;
    }

    /**
     * @return \EonX\EasyWebhook\Common\Entity\WebhookInterface[]
     */
    public function getDispatchedWebhooks(): array
    {
        return $this->dispatched;
    }
}
