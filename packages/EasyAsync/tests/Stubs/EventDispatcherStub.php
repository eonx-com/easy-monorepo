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

    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return EasyAsyncEventInterface[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
