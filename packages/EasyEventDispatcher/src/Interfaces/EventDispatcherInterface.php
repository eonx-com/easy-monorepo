<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Interfaces;

interface EventDispatcherInterface
{
    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch($event);
}
