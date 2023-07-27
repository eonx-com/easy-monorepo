<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Laravel\Stubs;

use Illuminate\Contracts\Events\Dispatcher;

final class LaravelEventDispatcherStub implements Dispatcher
{
    /**
     * @var array<int, object>
     */
    private array $dispatched = [];

    /**
     * @param object $event
     * @param bool|null $halt
     *
     * @return array<int, object>
     */
    public function dispatch(mixed $event, mixed $payload = null, mixed $halt = null): array
    {
        $this->dispatched[] = $event;

        return [$event];
    }

    /**
     * @param string $event
     */
    public function flush(mixed $event): void
    {
        // No body needed
    }

    /**
     * @param string $event
     */
    public function forget(mixed $event): void
    {
        // No body needed
    }

    public function forgetPushed(): void
    {
        // No body needed
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }

    /**
     * @param string $eventName
     */
    public function hasListeners(mixed $eventName): bool
    {
        return false;
    }

    /**
     * @param string|string[] $events
     */
    public function listen(mixed $events, mixed $listener = null): void
    {
        // No body needed
    }

    /**
     * @param string $event
     */
    public function push(mixed $event, mixed $payload = null): void
    {
        // No body needed
    }

    /**
     * @param object|string $subscriber
     */
    public function subscribe(mixed $subscriber): void
    {
        // No body needed
    }

    /**
     * @param string|object $event
     */
    public function until(mixed $event, mixed $payload = null)
    {
        return null;
    }
}
