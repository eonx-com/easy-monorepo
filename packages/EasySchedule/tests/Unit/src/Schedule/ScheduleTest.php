<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Unit\Schedule;

use EonX\EasySchedule\Schedule\Schedule;
use EonX\EasySchedule\Tests\Stub\Provider\ScheduleProviderStub;
use EonX\EasySchedule\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Console\Application;

final class ScheduleTest extends AbstractUnitTestCase
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
        $entry = $schedule->command('command:foo', [
            '--foo' => 'bar',
        ]);

        self::assertSame("'command:foo' --foo=bar", $entry->getDescription());
    }

    public function testGetDueEntries(): void
    {
        $schedule = new Schedule();
        $schedule->command('command:foo', [
            '--foo' => 'bar',
        ]);

        self::assertCount(1, $schedule->getDueEntries());
    }

    public function testSetApplication(): void
    {
        $app = new Application();
        $schedule = (new Schedule())->setApplication($app);

        self::assertSame($app, $schedule->getApplication());
    }
}
