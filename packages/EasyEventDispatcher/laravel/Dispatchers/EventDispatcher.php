<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Laravel\Dispatchers;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;

final class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private IlluminateDispatcherContract $decorated,
    ) {
    }

    public function dispatch(object $event): object
    {
        $this->decorated->dispatch($event);

        return $event;
    }
}
