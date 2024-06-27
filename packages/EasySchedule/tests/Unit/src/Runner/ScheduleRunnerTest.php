<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Unit\Runner;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasySchedule\Event\CommandExecutedEvent;
use EonX\EasySchedule\Runner\ScheduleRunner;
use EonX\EasySchedule\Schedule\Schedule;
use EonX\EasySchedule\Tests\Unit\AbstractUnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockInterface;

final class ScheduleRunnerTest extends AbstractUnitTestCase
{
    use ProphecyTrait;

    public function testRunSucceedsAndDispatchesEvent(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);
        $entry1 = $schedule->command('list', ['-q']);
        $entry2 = $schedule->command('list', ['-q']);
        $lockerProphecy = $this->prophesize(LockerInterface::class);
        $lockerProphecy->createLock($entry1->getLockResource(), $entry1->getMaxLockTime())
            ->shouldBeCalled();
        $lockProphecy = $this->prophesize(LockInterface::class);
        $lockProphecy->acquire()
            ->willReturn(true);
        $lockProphecy->release()
            ->shouldBeCalled();
        $lockerProphecy->createLock($entry2->getLockResource(), $entry2->getMaxLockTime())
            ->willReturn($lockProphecy);
        /** @var \EonX\EasyLock\Common\Locker\LockerInterface $locker */
        $locker = $lockerProphecy->reveal();
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($entry1))
            ->shouldBeCalled();
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($entry2))
            ->shouldBeCalled();
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $eventDispatcherProphecy->reveal();
        /** @var \Symfony\Component\Console\Output\OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class)->reveal();
        $scheduleRunner = new ScheduleRunner($eventDispatcher, $locker);

        $scheduleRunner->run($schedule, $output);

        self::assertCount(2, $schedule->getDueEntries());
    }
}
