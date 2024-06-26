<?php
declare(strict_types=1);

namespace EonX\EasySchedule;

use EonX\EasySchedule\Interfaces\EventInterface;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use UnexpectedValueException;

final class Schedule implements ScheduleInterface
{
    private Application $app;

    /**
     * @var \EonX\EasySchedule\Interfaces\EventInterface[]
     */
    private array $events = [];

    /**
     * @param \EonX\EasySchedule\Interfaces\ScheduleProviderInterface[] $providers
     */
    public function addProviders(array $providers): ScheduleInterface
    {
        foreach ($providers as $provider) {
            $provider->schedule($this);
        }

        return $this;
    }

    /**
     * @param class-string<\Symfony\Component\Console\Command\Command>|string $command
     */
    public function command(string $command, ?array $parameters = null): EventInterface
    {
        $commandName = $command;

        if (\is_a($command, Command::class, true)) {
            $commandName = $command::getDefaultName() ?? '';
        }

        if ($commandName === '') {
            throw new UnexpectedValueException('Command name cannot be empty.');
        }

        $event = new Event($commandName, $parameters);
        $this->events[] = $event;

        return $event;
    }

    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @return \EonX\EasySchedule\Interfaces\EventInterface[]
     */
    public function getDueEvents(): array
    {
        return \array_filter($this->events, static fn (EventInterface $event): bool => $event->isDue());
    }

    public function setApplication(Application $app): ScheduleInterface
    {
        $this->app = $app;
        $this->app->setAutoExit(false);

        return $this;
    }
}
