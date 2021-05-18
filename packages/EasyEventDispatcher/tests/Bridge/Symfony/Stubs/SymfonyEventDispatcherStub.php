<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Symfony\Stubs;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private $dispatched = [];

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param object      $event     The event to pass to the event handlers/listeners
     * @param string|null $eventName The name of the event to dispatch. If not supplied,
     *                               the class of $event should be used instead.
     *
     * @return object The passed $event MUST be returned
     */
    public function dispatch(object $event, string $eventName = null): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
