<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
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

    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface
    {
        return $this->decorated->dispatch($event);
    }
}
