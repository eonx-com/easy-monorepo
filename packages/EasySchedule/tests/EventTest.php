<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Tests;

use DateTimeZone;
use EonX\EasySchedule\Event;

final class EventTest extends AbstractTestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new Event('command:foo', [
            '--foo' => 'bar',
        ]);
    }

    /**
     * @return iterable<mixed>
     *
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
     * @return iterable<mixed>
     *
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
        self::assertFalse($this->event->allowsOverlapping());
        self::assertTrue($this->event->setAllowOverlapping(true)->allowsOverlapping());
    }

    /**
     * @param mixed[] $filters
     * @param mixed[] $rejects
     *
     * @dataProvider providerTestFiltersPass
     */
    public function testFiltersPass(array $filters, array $rejects, bool $expected): void
    {
        $event = new Event('command:foo', [
            '--foo' => 'bar',
        ]);

        foreach ($rejects as $reject) {
            $event->skip($reject);
        }
        foreach ($filters as $filter) {
            $event->when($filter);
        }

        self::assertSame($expected, $event->filtersPass());
    }

    public function testGetDescription(): void
    {
        self::assertSame("'command:foo' --foo=bar", $this->event->getDescription());
    }

    public function testGetLockResource(): void
    {
        $resource = \sprintf('sf-schedule-%s', \sha1($this->event->getCronExpression() . 'command:foo'));

        self::assertSame($resource, $this->event->getLockResource());
    }

    public function testMaxLockTime(): void
    {
        self::assertSame(60.0, $this->event->getMaxLockTime());
        self::assertSame(30.0, $this->event->setMaxLockTime(30.0)->getMaxLockTime());
    }

    /**
     * @param null|mixed[] $params
     *
     * @dataProvider providerTestNoArgsMethods
     */
    public function testNoArgsMethods(string $expression, string $method, ?array $params = null): void
    {
        $event = new Event('command:foo', [
            '--foo' => 'bar',
        ]);

        // Ok this is for coverage only, please don't judge me
        $event->before(function (): void {
        })
            ->then(function (): void {
            });

        $params ? $event->{$method}(...$params) : $event->{$method}();

        self::assertSame($expression, $event->getCronExpression());
    }

    public function testTimezone(): void
    {
        $timezone = new DateTimeZone('Australia/Melbourne');

        self::assertSame('UTC', $this->event->setTimezone('utc')->getTimezone());
        self::assertSame('Australia/Melbourne', $this->event->setTimezone($timezone)->getTimezone());
    }

    public function testWeekdaysDaily(): void
    {
        self::assertSame('0 0 * * 1-5', $this->event->weekdays()->daily()->getCronExpression());
    }

    public function testWeekdaysHourly(): void
    {
        self::assertSame('0 * * * 1-5', $this->event->weekdays()->hourly()->getCronExpression());
        self::assertIsBool($this->event->isDue());
    }
}
