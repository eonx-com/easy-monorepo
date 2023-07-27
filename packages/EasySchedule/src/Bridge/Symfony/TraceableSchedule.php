<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony;

use EonX\EasySchedule\Bridge\Symfony\Interfaces\TraceableScheduleInterface;
use EonX\EasySchedule\Interfaces\EventInterface;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use Symfony\Component\Console\Application;

final class TraceableSchedule implements TraceableScheduleInterface
{
    private string $currentProvider;

    /**
     * @var array<string, \EonX\EasySchedule\Interfaces\EventInterface[]>
     */
    private array $events = [];

    /**
     * @var \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[]
     */
    private array $providers = [];

    public function __construct(
        private ScheduleInterface $decorated,
    ) {
    }

    /**
     * @param \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[] $providers
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

    public function command(string $command, ?array $parameters = null): EventInterface
    {
        $event = $this->decorated->command($command, $parameters);

        if (isset($this->events[$this->currentProvider]) === false) {
            $this->events[$this->currentProvider] = [];
        }

        $this->events[$this->currentProvider][] = $event;

        return $event;
    }

    public function getApplication(): Application
    {
        return $this->decorated->getApplication();
    }

    /**
     * @return \EonX\EasySchedule\Interfaces\EventInterface[]
     */
    public function getDueEvents(): array
    {
        return $this->decorated->getDueEvents();
    }

    /**
     * @return array<string, \EonX\EasySchedule\Interfaces\EventInterface[]>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @return \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[]
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
