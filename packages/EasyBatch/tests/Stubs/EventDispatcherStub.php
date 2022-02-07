<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stubs;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private $dispatched = [];

    /**
     * @param object $event
     */
    public function dispatch($event): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return object[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }
}
