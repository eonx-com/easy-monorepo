<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Interfaces;

interface ScheduleProviderInterface
{
    public function schedule(ScheduleInterface $schedule): void;
}
