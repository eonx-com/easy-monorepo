<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface[]
     */
    private $dispatched = [];

    /**
     * Dispatch given event.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface $event
     *
     * @return \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface
     */
    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * Get dispatched events.
     *
     * @return \EonX\EasyAsync\Interfaces\EasyAsyncEventInterface[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
