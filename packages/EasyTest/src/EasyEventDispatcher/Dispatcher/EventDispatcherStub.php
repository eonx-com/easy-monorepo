<?php
declare(strict_types=1);

namespace EonX\EasyTest\EasyEventDispatcher\Dispatcher;

use Closure;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function __construct(
        private readonly EventDispatcherInterface $decorated,
    ) {
    }

    /**
     * @param class-string<object> $class
     */
    public function addDispatchCallback(string $class, Closure $callback): void
    {
        $this->dispatchCallbacks[$class] = $callback;
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->events[] = $event;

        $callback = $this->dispatchCallbacks[$event::class] ?? null;

        if ($callback !== null) {
            $callback($event, $eventName);
        }

        return $this->decorated->dispatch($event, $eventName);
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->events;
    }
}
