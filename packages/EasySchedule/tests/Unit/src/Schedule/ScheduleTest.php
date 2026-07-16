<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Unit\Schedule;

use EonX\EasySchedule\Schedule\Schedule;
use EonX\EasySchedule\Tests\Stub\Command\CommandStub;
use EonX\EasySchedule\Tests\Stub\Command\InvokableCommandStub;
use EonX\EasySchedule\Tests\Stub\Command\NoAttributeCommandStub;
use EonX\EasySchedule\Tests\Stub\Provider\ScheduleProviderStub;
use EonX\EasySchedule\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use UnexpectedValueException;

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

    public function testCommandResolvesClassNameFromAsCommandAttribute(): void
    {
        $schedule = new Schedule();
        $entry = $schedule->command(CommandStub::class);

        self::assertSame("'command:bar'", $entry->getDescription());
    }

    public function testCommandResolvesInvokableClassNameFromAsCommandAttribute(): void
    {
        $schedule = new Schedule();
        $entry = $schedule->command(InvokableCommandStub::class);

        self::assertSame("'command:invokable'", $entry->getDescription());
    }

    public function testCommandThrowsExceptionWhenClassHasNoAsCommandAttribute(): void
    {
        $schedule = new Schedule();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(\sprintf(
            'Command class "%s" does not have the "%s" attribute.',
            NoAttributeCommandStub::class,
            AsCommand::class
        ));

        $schedule->command(NoAttributeCommandStub::class);
    }

    public function testCommandThrowsExceptionWhenNameIsEmpty(): void
    {
        $schedule = new Schedule();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Command name cannot be empty.');

        $schedule->command('');
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
        $schedule = new Schedule()
            ->setApplication($app);

        self::assertSame($app, $schedule->getApplication());
    }
}
