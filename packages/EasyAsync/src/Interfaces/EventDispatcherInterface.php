<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface EventDispatcherInterface
{
    /**
     * Dispatch given event.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface $event
     *
     * @return \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface
     */
    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface;
}
