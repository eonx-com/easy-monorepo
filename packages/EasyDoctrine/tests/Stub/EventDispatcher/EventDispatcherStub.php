<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stub\EventDispatcher;

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
     */
    public function addDispatchCallback(string $class, Closure $callback): void
    {
        $this->dispatchCallbacks[$class] = $callback;
    }

    public function dispatch(object $event): object
    {
        $this->events[] = $event;

        $callback = $this->dispatchCallbacks[$event::class] ?? null;

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
