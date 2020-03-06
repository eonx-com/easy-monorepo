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

    public function __construct(Dispatcher $decorated)
    {
        $this->decorated = $decorated;
    }

    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface
    {
        $this->decorated->dispatch($event);

        return $event;
    }
}
