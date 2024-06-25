<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Runner;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasySchedule\Event\CommandExecutedEvent;
use EonX\EasySchedule\Schedule\ScheduleInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ScheduleRunner implements ScheduleRunnerInterface
{
    private bool $ran = false;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LockerInterface $locker,
    ) {
    }

    public function run(ScheduleInterface $schedule, OutputInterface $output): void
    {
        foreach ($schedule->getDueEntries() as $entry) {
            if ($entry->filtersPass() === false) {
                continue;
            }

            $this->ran = true;

            $description = $entry->getDescription();
            $lock = $this->locker->createLock($entry->getLockResource(), $entry->getMaxLockTime());

            $output->writeln(\sprintf('<info>Running scheduled command:</info> %s', $description));

            if ($entry->allowsOverlapping() === false && $lock->acquire() === false) {
                $output->writeln(\sprintf('Abort execution of "%s" to prevent overlapping', $description));

                continue;
            }

            try {
                $entry->run($schedule->getApplication());
            } finally {
                $this->eventDispatcher->dispatch(new CommandExecutedEvent($entry));
                $lock->release();
            }
        }

        if ($this->ran === false) {
            $output->writeln('<info>No scheduled commands are ready to run.</info>');
        }
    }
}
