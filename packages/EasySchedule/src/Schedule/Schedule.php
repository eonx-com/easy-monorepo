<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Schedule;

use EonX\EasySchedule\Entry\ScheduleEntry;
use EonX\EasySchedule\Entry\ScheduleEntryInterface;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use UnexpectedValueException;

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

    /**
     * @param class-string|string $command
     */
    public function command(string $command, ?array $parameters = null): ScheduleEntryInterface
    {
        $commandName = $this->resolveCommandName($command);

        if ($commandName === '') {
            throw new UnexpectedValueException('Command name cannot be empty.');
        }

        $entry = new ScheduleEntry($commandName, $parameters);
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

    /**
     * Resolves the console command name from a command name string or a command class-string. The name is read from
     * the #[AsCommand] attribute, which supports both invokable commands (classes that do not extend Command) and
     * classes extending Command, without relying on the deprecated Command::getDefaultName() method.
     *
     * @param class-string|string $command
     */
    private function resolveCommandName(string $command): string
    {
        if (\class_exists($command)) {
            $attributes = (new ReflectionClass($command))->getAttributes(AsCommand::class);

            if (isset($attributes[0])) {
                return $attributes[0]->newInstance()->name;
            }

            if (\is_a($command, Command::class, true)) {
                return $command::getDefaultName() ?? '';
            }
        }

        return $command;
    }
}
