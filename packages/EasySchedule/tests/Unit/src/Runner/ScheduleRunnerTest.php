<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Unit\Runner;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
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

    public function testRunSucceedsAndDispatchesEntry(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);
        $entry1 = $schedule->command('list', ['-q']);
        $entry2 = $schedule->command('list', ['-q']);
        $lockServiceProphecy = $this->prophesize(LockServiceInterface::class);
        $lockServiceProphecy->createLock($entry1->getLockResource(), $entry1->getMaxLockTime())
            ->shouldBeCalled();
        $lockProphecy = $this->prophesize(LockInterface::class);
        $lockProphecy->acquire()
            ->willReturn(true);
        $lockProphecy->release()
            ->shouldBeCalled();
        $lockServiceProphecy->createLock($entry2->getLockResource(), $entry2->getMaxLockTime())
            ->willReturn($lockProphecy);
        /** @var \EonX\EasyLock\Interfaces\LockServiceInterface $lockService */
        $lockService = $lockServiceProphecy->reveal();
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($entry1))
            ->shouldBeCalled();
        $eventDispatcherProphecy->dispatch(new CommandExecutedEvent($entry2))
            ->shouldBeCalled();
        /** @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $eventDispatcherProphecy->reveal();
        /** @var \Symfony\Component\Console\Output\OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class)->reveal();
        $scheduleRunner = new ScheduleRunner($eventDispatcher, $lockService);

        $scheduleRunner->run($schedule, $output);

        self::assertCount(2, $schedule->getDueEntries());
    }
}
