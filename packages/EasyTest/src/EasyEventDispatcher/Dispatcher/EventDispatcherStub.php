<?php
declare(strict_types=1);

namespace EonX\EasyTest\EasyEventDispatcher\Dispatcher;

use Closure;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;

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

    public function dispatch(object $event): object
    {
        $this->events[] = $event;

        $callback = $this->dispatchCallbacks[$event::class] ?? null;

        if ($callback !== null) {
            $callback($event);
        }

        return $this->decorated->dispatch($event);
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->events;
    }
}
