<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stub;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]|\EonX\EasyAsync\Common\Event\EasyAsyncEventInterface[]
     */
    private array $dispatched = [];

    public function dispatch(object $event): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return object[]|\EonX\EasyAsync\Common\Event\EasyAsyncEventInterface[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
