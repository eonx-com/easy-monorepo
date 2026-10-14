<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Common\Clock;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use EonX\EasyTest\Common\Clock\CarbonClock;
use PHPUnit\Framework\TestCase;

final class CarbonClockTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testItFollowsTheCarbonTestTime(): void
    {
        $clock = new CarbonClock();
        Carbon::setTestNow('2035-01-01 00:00:00');

        self::assertSame('2035-01-01 00:00:00', $clock->now()->format('Y-m-d H:i:s'));

        CarbonImmutable::setTestNow('2035-06-01 12:00:00');

        self::assertSame('2035-06-01 12:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function testItMovesTheCarbonTestTimeByAMicrosecond(): void
    {
        $clock = new CarbonClock();
        CarbonImmutable::setTestNow('2035-01-01 00:00:00');

        $clock->sleep(0.000001);

        self::assertSame('2035-01-01 00:00:00.000001', CarbonImmutable::now()->format('Y-m-d H:i:s.u'));
    }

    public function testItMovesTheCarbonTestTimeOnSleep(): void
    {
        $clock = new CarbonClock();
        CarbonImmutable::setTestNow('2035-01-01 00:00:00');

        $clock->sleep(10.000001);

        self::assertSame('2035-01-01 00:00:10.000001', Carbon::now()->format('Y-m-d H:i:s.u'));
        self::assertSame('2035-01-01 00:00:10.000001', CarbonImmutable::now()->format('Y-m-d H:i:s.u'));
    }

    public function testItNeverMovesTheCarbonTestTimeBackwards(): void
    {
        $clock = new CarbonClock();
        CarbonImmutable::setTestNow('2035-01-01 00:00:00');

        $clock->sleep(-10);
        $clock->sleep(0);

        self::assertSame('2035-01-01 00:00:00.000000', CarbonImmutable::now()->format('Y-m-d H:i:s.u'));
    }

    public function testItSharesTheCarbonTestTimeAcrossTimeZones(): void
    {
        $clock = new CarbonClock();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2035-01-01 00:00:00', 'UTC'));
        $sydneyClock = $clock->withTimeZone('Australia/Sydney');

        $sydneyClock->sleep(60);

        self::assertSame('2035-01-01 11:01:00 AEDT', $sydneyClock->now()->format('Y-m-d H:i:s T'));
        self::assertSame('2035-01-01 00:01:00 UTC', $clock->now()->setTimezone('UTC')->format('Y-m-d H:i:s T'));

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2035-01-01 00:10:00', 'UTC'));

        self::assertSame('2035-01-01 11:10:00 AEDT', $sydneyClock->now()->format('Y-m-d H:i:s T'));
    }
}
