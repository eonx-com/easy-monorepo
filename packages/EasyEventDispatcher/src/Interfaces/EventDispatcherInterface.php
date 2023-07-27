<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Interfaces;

interface EventDispatcherInterface
{
    public function dispatch(object $event): object;
}
