<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Interfaces;

use Symfony\Component\Console\Output\OutputInterface;

interface ScheduleRunnerInterface
{
    public function run(ScheduleInterface $schedule, OutputInterface $output): void;
}
