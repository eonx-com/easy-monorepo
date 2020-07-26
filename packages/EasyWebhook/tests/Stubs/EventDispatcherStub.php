<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var mixed[]
     */
    private $dispatched = [];

    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch($event)
    {
        $this->dispatched[] = $event;

        return $event;
    }
}
