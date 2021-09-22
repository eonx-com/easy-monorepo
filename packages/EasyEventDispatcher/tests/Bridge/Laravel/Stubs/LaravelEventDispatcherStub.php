<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Laravel\Stubs;

use Illuminate\Contracts\Events\Dispatcher;

final class LaravelEventDispatcherStub implements Dispatcher
{
    /**
     * @var object[]
     */
    private $dispatched = [];

    /**
     * @param object $event
     * @param mixed $payload
     *
     * @return null|mixed[]
     */
    public function dispatch($event, $payload = null, $halt = null)
    {
        $this->dispatched[] = $event;

        return [$event];
    }

    /**
     * @param string $event
     */
    public function flush($event): void
    {
        // No body needed.
    }

    /**
     * @param string $event
     */
    public function forget($event): void
    {
        // No body needed.
    }

    public function forgetPushed(): void
    {
        // No body needed.
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
    public function hasListeners($eventName): bool
    {
        return false;
    }

    /**
     * @param string|string[] $events
     * @param mixed $listener
     */
    public function listen($events, $listener = null): void
    {
        // No body needed.
    }

    /**
     * @param string $event
     * @param mixed[] $payload
     */
    public function push($event, $payload = null): void
    {
        // No body needed.
    }

    /**
     * @param object|string $subscriber
     */
    public function subscribe($subscriber): void
    {
        // No body needed.
    }

    /**
     * @param string|object $event
     * @param mixed $payload
     *
     * @return mixed|null
     */
    public function until($event, $payload = null)
    {
        return null;
    }
}
