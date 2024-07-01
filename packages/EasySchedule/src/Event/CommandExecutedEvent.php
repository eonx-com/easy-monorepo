<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Event;

use EonX\EasySchedule\Entry\ScheduleEntryInterface;

final readonly class CommandExecutedEvent
{
    public function __construct(
        private ScheduleEntryInterface $scheduleEntry,
    ) {
    }

    public function getScheduleEntry(): ScheduleEntryInterface
    {
        return $this->scheduleEntry;
    }
}
