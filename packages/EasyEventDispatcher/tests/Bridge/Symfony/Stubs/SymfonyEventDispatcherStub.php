<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Symfony\Stubs;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventDispatcherStub implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private $dispatched = [];

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @return object[]
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatched;
    }
}
