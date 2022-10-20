<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stubs;

use Closure;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var array<string, callable>
     */
    private array $dispatchCallbacks = [];

    /**
     * @var object[]
     */
    private array $events = [];

    /**
     * @param class-string $class
     * @param Closure(mixed): void $callback
     */
    public function addDispatchCallback(string $class, Closure $callback): void
    {
        $this->dispatchCallbacks[$class] = $callback;
    }

    /**
     * @param object $event
     */
    public function dispatch($event)
    {
        $this->events[] = $event;

        $callback = $this->dispatchCallbacks[\get_class($event)] ?? null;
        if ($callback !== null) {
            $callback($event);
        }

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
