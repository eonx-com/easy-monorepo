<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Events;

use EonX\EasySchedule\Interfaces\EventInterface;

final readonly class CommandExecutedEvent
{
    public function __construct(
        private EventInterface $scheduleEvent,
    ) {
    }

    public function getScheduleEvent(): EventInterface
    {
        return $this->scheduleEvent;
    }
}
