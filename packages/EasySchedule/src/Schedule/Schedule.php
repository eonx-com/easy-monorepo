<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Schedule;

use EonX\EasySchedule\Entry\ScheduleEntry;
use EonX\EasySchedule\Entry\ScheduleEntryInterface;
use Symfony\Component\Console\Application;

final class Schedule implements ScheduleInterface
{
    private Application $app;

    /**
     * @var \EonX\EasySchedule\Entry\ScheduleEntryInterface[]
     */
    private array $entries = [];

    /**
     * @param \EonX\EasySchedule\Provider\ScheduleProviderInterface[] $providers
     */
    public function addProviders(array $providers): ScheduleInterface
    {
        foreach ($providers as $provider) {
            $provider->schedule($this);
        }

        return $this;
    }

    public function command(string $command, ?array $parameters = null): ScheduleEntryInterface
    {
        $entry = new ScheduleEntry($command, $parameters);
        $this->entries[] = $entry;

        return $entry;
    }

    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @return \EonX\EasySchedule\Entry\ScheduleEntryInterface[]
     */
    public function getDueEntries(): array
    {
        return \array_filter($this->entries, static fn (ScheduleEntryInterface $entry): bool => $entry->isDue());
    }

    public function setApplication(Application $app): ScheduleInterface
    {
        $this->app = $app;
        $this->app->setAutoExit(false);

        return $this;
    }
}
