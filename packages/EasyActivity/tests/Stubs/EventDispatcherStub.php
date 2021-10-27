<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private $events = [];

    /**
     * @param object $event
     */
    public function dispatch($event)
    {
        $this->events[] = $event;

        return $event;
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->events;
    }
}
