<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $decorated;

    /**
     * EventDispatcher constructor.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $decorated
     */
    public function __construct(Dispatcher $decorated)
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
        $this->decorated->dispatch($event);

        return $event;
    }
}
