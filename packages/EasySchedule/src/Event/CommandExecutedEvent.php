<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Event;

use EonX\EasySchedule\Interfaces\EventInterface;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleRunnerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
