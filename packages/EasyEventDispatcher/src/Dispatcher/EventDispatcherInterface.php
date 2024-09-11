<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Dispatcher;

/**
 * @internal See `packages/EasyEventDispatcher/readme.md` for details.
 */
interface EventDispatcherInterface
{
    public function dispatch(object $event): object;
}
