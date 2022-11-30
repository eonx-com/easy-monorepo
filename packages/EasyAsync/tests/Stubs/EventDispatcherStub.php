<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]|\EonX\EasyAsync\Interfaces\EasyAsyncEventInterface[]
     */
    private $dispatched = [];

    public function dispatch($event): EasyAsyncEventInterface
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return object[]|\EonX\EasyAsync\Interfaces\EasyAsyncEventInterface[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
