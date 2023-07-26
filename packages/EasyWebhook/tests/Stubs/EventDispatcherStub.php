<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyWebhook\Interfaces\WebhookEventInterface;
use InvalidArgumentException;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookEventInterface[]
     */
    private array $dispatched = [];

    public function dispatch(object $event): object
    {
        if ($event instanceof WebhookEventInterface === false) {
            throw new InvalidArgumentException(\sprintf(
                'Event must be instance of "%s", "%s" given.',
                WebhookEventInterface::class,
                $event::class
            ));
        }

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
