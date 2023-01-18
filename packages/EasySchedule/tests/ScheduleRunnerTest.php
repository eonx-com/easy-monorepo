<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Tests;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasySchedule\Schedule;
use EonX\EasySchedule\ScheduleRunner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockInterface;

final class ScheduleRunnerTest extends AbstractTestCase
{
    public function testRunSucceedsAndClearsEntityManager(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);
        $event1 = $schedule->command('list', ['-q']);
        $event2 = $schedule->command('list', ['-q']);
        $lockServiceProphecy = $this->prophesize(LockServiceInterface::class);
        $lockServiceProphecy->createLock($event1->getLockResource(), $event1->getMaxLockTime())
            ->shouldBeCalled();
        $lockProphecy = $this->prophesize(LockInterface::class);
        $lockProphecy->acquire()
            ->willReturn(true);
        $lockProphecy->release()
            ->shouldBeCalled();
        $lockServiceProphecy->createLock($event2->getLockResource(), $event2->getMaxLockTime())
            ->willReturn($lockProphecy);
        /** @var \EonX\EasyLock\Interfaces\LockServiceInterface $lockService */
        $lockService = $lockServiceProphecy->reveal();
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->clear()
            ->shouldBeCalledTimes(2);
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $entityManagerProphecy->reveal();
        /** @var \Symfony\Component\Console\Output\OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class)->reveal();
        $scheduleRunner = new ScheduleRunner($lockService, $entityManager);

        $scheduleRunner->run($schedule, $output);

        self::assertCount(2, $schedule->getDueEvents());
    }
}
