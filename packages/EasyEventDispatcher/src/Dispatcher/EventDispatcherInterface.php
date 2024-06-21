<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Dispatcher;

interface EventDispatcherInterface
{
    public function dispatch(object $event): object;
}
