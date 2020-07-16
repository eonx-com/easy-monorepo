<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Symfony;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $decorated;

    public function __construct(SymfonyEventDispatcherInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch($event)
    {
        return $this->decorated->dispatch($event);
    }
}
