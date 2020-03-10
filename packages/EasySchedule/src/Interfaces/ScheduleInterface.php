<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Interfaces;

use Symfony\Component\Console\Application;

interface ScheduleInterface
{
    /**
     * @param \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[] $providers
     */
    public function addProviders(array $providers): self;

    /**
     * @param null|mixed[] $parameters
     */
    public function command(string $command, ?array $parameters = null): EventInterface;

    public function getApplication(): Application;

    /**
     * @return \EonX\EasySchedule\Interfaces\EventInterface[]
     */
    public function getDueEvents(): array;

    public function setApplication(Application $app): self;
}
