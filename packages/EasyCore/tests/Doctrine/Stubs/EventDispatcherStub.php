<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Stubs;

use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventDispatcherStub extends EventDispatcher
{
    /**
     * @var object[]
     */
    private $events = [];

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->events[] = $event;

        return parent::dispatch($event, $eventName);
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->events;
    }
}
