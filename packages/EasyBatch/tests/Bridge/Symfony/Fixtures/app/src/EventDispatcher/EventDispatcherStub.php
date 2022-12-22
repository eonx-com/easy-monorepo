<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\EventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;

final class EventDispatcherStub implements EventDispatcherInterface, ResetInterface
{
    /**
     * @var object[]
     */
    private array $dispatched = [];

    public function __construct(private EventDispatcherInterface $decorated)
    {
    }

    /**
     * @param object $event The event to pass to the event handlers/listeners
     *
     * @return object The passed $event MUST be returned
     */
    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->dispatched[] = $event;

        return $this->decorated->dispatch($event, $eventName);
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }

    public function reset(): void
    {
        $this->dispatched = [];
    }
}
