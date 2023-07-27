<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private array $dispatchedEvents = [];

    public function dispatch(object $event): object
    {
        $this->dispatchedEvents[] = $event;

        return $event;
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
