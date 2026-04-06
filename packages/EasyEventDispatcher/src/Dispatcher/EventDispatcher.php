<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Dispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private SymfonyEventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dispatch(object $event): object
    {
        return $this->eventDispatcher->dispatch($event);
    }

    public function dispatchWithName(object $event, string $eventName): object
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
