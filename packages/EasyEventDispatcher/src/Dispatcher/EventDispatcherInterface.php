<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Dispatcher;

/**
 * @internal See readme.md for details.
 */
interface EventDispatcherInterface
{
    public function dispatch(object $event): object;

    public function dispatchWithName(object $event, string $eventName): object;
}
