<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $decorated;

    public function __construct(IlluminateDispatcherContract $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch($event)
    {
        $this->decorated->dispatch($event);

        return $event;
    }
}
