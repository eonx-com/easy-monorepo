<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Stub\Provider;

use EonX\EasySchedule\Provider\ScheduleProviderInterface;
use EonX\EasySchedule\Schedule\ScheduleInterface;

final class ScheduleProviderStub implements ScheduleProviderInterface
{
    private ScheduleInterface $schedule;

    public function getSchedule(): ScheduleInterface
    {
        return $this->schedule;
    }

    public function schedule(ScheduleInterface $schedule): void
    {
        $this->schedule = $schedule;
    }
}
