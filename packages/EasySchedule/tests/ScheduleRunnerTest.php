<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasySchedule\Events\CommandExecutedEvent;
use EonX\EasySchedule\Schedule;
use EonX\EasySchedule\ScheduleRunner;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockInterface;

final class ScheduleRunnerTest extends AbstractTestCase
{
    use ProphecyTrait;

    public function testRunSucceedsAndDispatchesEvent(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);
        $event1 = $schedule->command('list', ['-q']);
        $event2 = $schedule->command('list', ['-q']);
        $lockerProphecy = $this->prophesize(LockerInterface::class);
        $lockerProphecy->createLock($event1->getLockResource(), $event1->getMaxLockTime())
            ->shouldBeCalled();
        $lockProphecy = $this->prophesize(LockInterface::class);
        $lockProphecy->acquire()
            ->willReturn(true);
        $lockProphecy->release()
            ->shouldBeCalled();
        $lockerProphecy->createLock($event2->getLockResource(), $event2->getMaxLockTime())
            ->willReturn($lockProphecy);
        /** @var \EonX\EasyLock\Common\Locker\LockerInterface $locker */
        $locker = $lockerProphecy->reveal();
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($event1))
            ->shouldBeCalled();
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($event2))
            ->shouldBeCalled();
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $eventDispatcherProphecy->reveal();
        /** @var \Symfony\Component\Console\Output\OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class)->reveal();
        $scheduleRunner = new ScheduleRunner($eventDispatcher, $locker);

        $scheduleRunner->run($schedule, $output);

        self::assertCount(2, $schedule->getDueEvents());
    }
}
