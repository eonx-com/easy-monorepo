<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Schedule;

use EonX\EasySchedule\Entry\ScheduleEntryInterface;
use Symfony\Component\Console\Application;

interface ScheduleInterface
{
    /**
     * @param \EonX\EasySchedule\Provider\ScheduleProviderInterface[] $providers
     */
    public function addProviders(array $providers): self;

    /**
     * @param class-string<\Symfony\Component\Console\Command\Command>|string $command
     */
    public function command(string $command, ?array $parameters = null): ScheduleEntryInterface;

    public function getApplication(): Application;

    /**
     * @return \EonX\EasySchedule\Entry\ScheduleEntryInterface[]
     */
    public function getDueEntries(): array;

    public function setApplication(Application $app): self;
}
