<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Schedule;

use EonX\EasySchedule\Entry\ScheduleEntryInterface;
use Symfony\Component\Console\Application;

final class TraceableSchedule implements TraceableScheduleInterface
{
    private string $currentProvider;

    /**
     * @var array<string, \EonX\EasySchedule\Entry\ScheduleEntryInterface[]>
     */
    private array $entries = [];

    /**
     * @var \EonX\EasySchedule\Provider\ScheduleProviderInterface[]
     */
    private array $providers = [];

    public function __construct(
        private readonly ScheduleInterface $decorated,
    ) {
    }

    /**
     * @param \EonX\EasySchedule\Provider\ScheduleProviderInterface[] $providers
     */
    public function addProviders(array $providers): ScheduleInterface
    {
        foreach ($providers as $provider) {
            $this->currentProvider = $provider::class;
            $this->providers[] = $provider;

            $provider->schedule($this);
        }

        return $this;
    }

    public function command(string $command, ?array $parameters = null): ScheduleEntryInterface
    {
        $entry = $this->decorated->command($command, $parameters);

        if (isset($this->entries[$this->currentProvider]) === false) {
            $this->entries[$this->currentProvider] = [];
        }

        $this->entries[$this->currentProvider][] = $entry;

        return $entry;
    }

    public function getApplication(): Application
    {
        return $this->decorated->getApplication();
    }

    /**
     * @return \EonX\EasySchedule\Entry\ScheduleEntryInterface[]
     */
    public function getDueEntries(): array
    {
        return $this->decorated->getDueEntries();
    }

    /**
     * @return array<string, \EonX\EasySchedule\Entry\ScheduleEntryInterface[]>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @return \EonX\EasySchedule\Provider\ScheduleProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function setApplication(Application $app): ScheduleInterface
    {
        $this->decorated->setApplication($app);

        return $this;
    }
}
