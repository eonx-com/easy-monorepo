<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Event;

use EonX\EasySchedule\Interfaces\EventInterface;

final class CommandExecutedEvent
{
    public function __construct(private readonly EventInterface $event)
    {
    }

    public function getEvent(): EventInterface
    {
        return $this->event;
    }
}
