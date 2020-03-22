<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Stubs;

use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleProviderInterface;

final class ScheduleProviderStub implements ScheduleProviderInterface
{
    /**
     * @var \EonX\EasySchedule\Interfaces\ScheduleInterface
     */
    private $schedule;

    public function getSchedule(): ScheduleInterface
    {
        return $this->schedule;
    }

    public function schedule(ScheduleInterface $schedule): void
    {
        $this->schedule = $schedule;
    }
}
