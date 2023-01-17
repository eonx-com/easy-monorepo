<?php

declare(strict_types=1);

namespace EonX\EasySchedule;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleRunnerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ScheduleRunner implements ScheduleRunnerInterface
{
    private EntityManagerInterface $entityManager;

    private LockServiceInterface $lockService;

    private bool $ran = false;

    public function __construct(LockServiceInterface $lockService, EntityManagerInterface $entityManager)
    {
        $this->lockService = $lockService;
        $this->entityManager = $entityManager;
    }

    public function run(ScheduleInterface $schedule, OutputInterface $output): void
    {
        foreach ($schedule->getDueEvents() as $event) {
            if ($event->filtersPass() === false) {
                continue;
            }

            $this->ran = true;

            $description = $event->getDescription();
            $lock = $this->lockService->createLock($event->getLockResource(), $event->getMaxLockTime());

            $output->writeln(\sprintf('<info>Running scheduled command:</info> %s', $description));

            if ($event->allowsOverlapping() === false && $lock->acquire() === false) {
                $output->writeln(\sprintf('Abort execution of "%s" to prevent overlapping', $description));

                continue;
            }

            try {
                $event->run($schedule->getApplication());
            } finally {
                $this->entityManager->flush();
                $lock->release();
            }
        }

        if ($this->ran === false) {
            $output->writeln('<info>No scheduled commands are ready to run.</info>');
        }
    }
}
