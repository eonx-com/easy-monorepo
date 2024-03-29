<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Symfony;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private SymfonyEventDispatcherInterface $decorated,
    ) {
    }

    public function dispatch(object $event): object
    {
        return $this->decorated->dispatch($event);
    }
}
