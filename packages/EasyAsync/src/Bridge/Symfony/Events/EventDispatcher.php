<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $decorated;

    /**
     * EventDispatcher constructor.
     *
     * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $decorated
     */
    public function __construct(SymfonyEventDispatcherInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * Dispatch given event.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface $event
     *
     * @return \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface
     */
    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface
    {
        /** @var \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface $event */
        $event = $this->decorated->dispatch($event);

        return $event;
    }
}
