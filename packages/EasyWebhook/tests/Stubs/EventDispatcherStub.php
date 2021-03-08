<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookEventInterface[]
     */
    private $dispatched = [];

    /**
     * @param \EonX\EasyWebhook\Interfaces\WebhookEventInterface $event
     *
     * @return object
     */
    public function dispatch($event)
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookEventInterface[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }
}
