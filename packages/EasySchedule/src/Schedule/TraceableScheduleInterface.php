<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Schedule;

interface TraceableScheduleInterface extends ScheduleInterface
{
    /**
     * @return array<string, \EonX\EasySchedule\Entry\ScheduleEntryInterface[]>
     */
    public function getEntries(): array;

    /**
     * @return \EonX\EasySchedule\Provider\ScheduleProviderInterface[]
     */
    public function getProviders(): array;
}
