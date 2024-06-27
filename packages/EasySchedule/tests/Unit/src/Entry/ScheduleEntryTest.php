<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Tests\Unit\Entry;

use DateTimeZone;
use EonX\EasySchedule\Entry\ScheduleEntry;
use EonX\EasySchedule\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ScheduleEntryTest extends AbstractUnitTestCase
{
    private ScheduleEntry $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entry = new ScheduleEntry('command:foo', [
            '--foo' => 'bar',
        ]);
    }

    /**
     * @see testFiltersPass
     */
    public static function providerTestFiltersPass(): iterable
    {
        yield 'False because at least one filter false' => [[false], [false], false];

        yield 'False because at least one reject true' => [[true], [true], false];

        yield 'true because no filter false and no reject true' => [
            [true],
            [
                fn (): bool => false,
            ],
            true,
        ];
    }

    /**
     * @see testNoArgsMethods
     */
    public static function providerTestNoArgsMethods(): iterable
    {
        yield ['0 0 1 * *', 'monthly'];
        yield ['0 0 * * 0', 'weekly'];
        yield ['* * * * 0,6', 'weekends'];
        yield ['0 15 * * *', 'at', ['15:00']];
        yield ['0 0 * * *', 'daily'];
        yield ['*/5 * * * *', 'everyFiveMinutes'];
        yield ['* * * * *', 'everyMinute'];
        yield ['* * * * 5', 'fridays'];
        yield ['* * * * 1', 'mondays'];
        yield ['* * * * 6', 'saturdays'];
        yield ['* * * * 0', 'sundays'];
        yield ['* * * * 4', 'thursdays'];
        yield ['* * * * 2', 'tuesdays'];
        yield ['* * * * 3', 'wednesdays'];
        yield ['* * * * 1-5', 'weekdays'];
        yield ['0 15 4 * *', 'monthlyOn', [4, '15:00']];
        yield ['15 15 4 * *', 'monthlyOn', [4, '15:15']];
        yield ['37 * * * *', 'hourlyAt', [37]];
        yield ['15,30,45 * * * *', 'hourlyAt', [[15, 30, 45]]];
        yield ['0 0 1 1-12/3 *', 'quarterly'];
        yield ['0 3,15 * * *', 'twiceDaily', [3, 15]];
        yield ['0 0 1,16 * *', 'twiceMonthly', [1, 16]];
        yield ['0 15 * * 1', 'weeklyOn', [1, '15:00']];
        yield ['0 0 1 1 *', 'yearly'];
        yield ['*/15 * * * *', 'everyFifteenMinutes'];
        yield ['*/10 * * * *', 'everyTenMinutes'];
        yield ['0,30 * * * *', 'everyThirtyMinutes'];
    }

    public function testAllowsOverlapping(): void
    {
        self::assertFalse($this->entry->allowsOverlapping());
        self::assertTrue($this->entry->setAllowOverlapping(true)->allowsOverlapping());
    }

    #[DataProvider('providerTestFiltersPass')]
    public function testFiltersPass(array $filters, array $rejects, bool $expected): void
    {
        $entry = new ScheduleEntry('command:foo', [
            '--foo' => 'bar',
        ]);

        foreach ($rejects as $reject) {
            $entry->skip($reject);
        }
        foreach ($filters as $filter) {
            $entry->when($filter);
        }

        self::assertSame($expected, $entry->filtersPass());
    }

    public function testGetDescription(): void
    {
        self::assertSame("'command:foo' --foo=bar", $this->entry->getDescription());
    }

    public function testGetLockResource(): void
    {
        $resource = \sprintf('sf-schedule-%s', \sha1($this->entry->getCronExpression() . 'command:foo'));

        self::assertSame($resource, $this->entry->getLockResource());
    }

    public function testMaxLockTime(): void
    {
        self::assertSame(60.0, $this->entry->getMaxLockTime());
        self::assertSame(30.0, $this->entry->setMaxLockTime(30.0)->getMaxLockTime());
    }

    #[DataProvider('providerTestNoArgsMethods')]
    public function testNoArgsMethods(string $expression, string $method, ?array $params = null): void
    {
        $entry = new ScheduleEntry('command:foo', [
            '--foo' => 'bar',
        ]);

        // Ok this is for coverage only, please don't judge me
        $entry->before(function (): void {
        })
            ->then(function (): void {
            });

        $params ? $entry->{$method}(...$params) : $entry->{$method}();

        self::assertSame($expression, $entry->getCronExpression());
    }

    public function testTimezone(): void
    {
        $timezone = new DateTimeZone('Australia/Melbourne');

        self::assertSame('UTC', $this->entry->setTimezone('utc')->getTimezone());
        self::assertSame('Australia/Melbourne', $this->entry->setTimezone($timezone)->getTimezone());
    }

    public function testWeekdaysDaily(): void
    {
        self::assertSame('0 0 * * 1-5', $this->entry->weekdays()->daily()->getCronExpression());
    }

    public function testWeekdaysHourly(): void
    {
        self::assertSame('0 * * * 1-5', $this->entry->weekdays()->hourly()->getCronExpression());
        self::assertIsBool($this->entry->isDue());
    }
}
