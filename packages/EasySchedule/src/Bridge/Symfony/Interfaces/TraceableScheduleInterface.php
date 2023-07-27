<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony\Interfaces;

use EonX\EasySchedule\Interfaces\ScheduleInterface;

interface TraceableScheduleInterface extends ScheduleInterface
{
    /**
     * @return array<string, \EonX\EasySchedule\Interfaces\EventInterface[]>
     */
    public function getEvents(): array;

    /**
     * @return \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[]
     */
    public function getProviders(): array;
}
