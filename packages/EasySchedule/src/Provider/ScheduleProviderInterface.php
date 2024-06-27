<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Provider;

use EonX\EasySchedule\Schedule\ScheduleInterface;

interface ScheduleProviderInterface
{
    public function schedule(ScheduleInterface $schedule): void;
}
