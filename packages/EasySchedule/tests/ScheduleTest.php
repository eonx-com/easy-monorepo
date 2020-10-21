<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Tests;

use EonX\EasySchedule\Schedule;
use EonX\EasySchedule\Tests\Stubs\ScheduleProviderStub;
use Symfony\Component\Console\Application;

final class ScheduleTest extends AbstractTestCase
{
    public function testAddProviders(): void
    {
        $stub = new ScheduleProviderStub();
        $schedule = new Schedule();

        $schedule->addProviders([$stub]);

        self::assertSame($schedule, $stub->getSchedule());
    }

    public function testCommand(): void
    {
        $schedule = new Schedule();
        $event = $schedule->command('command:foo', [
            '--foo' => 'bar',
        ]);

        self::assertSame('\'command:foo\' --foo=bar', $event->getDescription());
    }

    public function testGetDueEvents(): void
    {
        $schedule = new Schedule();
        $schedule->command('command:foo', [
            '--foo' => 'bar',
        ]);

        self::assertCount(1, $schedule->getDueEvents());
    }

    public function testSetApplication(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);

        self::assertSame($app, $schedule->getApplication());
    }
}
