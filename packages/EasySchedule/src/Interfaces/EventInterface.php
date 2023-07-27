<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Interfaces;

use DateTimeZone;
use Symfony\Component\Console\Application;

interface EventInterface
{
    public function allowsOverlapping(): bool;

    public function at(string $time): self;

    public function before(callable $func): self;

    public function between(string $startTime, string $endTime): self;

    public function cron(string $expression): self;

    public function daily(): self;

    public function dailyAt(string $time): self;

    /**
     * @param int|int[] $days
     */
    public function days(int|array $days): self;

    public function everyFifteenMinutes(): self;

    public function everyFiveMinutes(): self;

    public function everyMinute(): self;

    public function everyTenMinutes(): self;

    public function everyThirtyMinutes(): self;

    public function filtersPass(): bool;

    public function fridays(): self;

    public function getCronExpression(): string;

    public function getDescription(): string;

    public function getLockResource(): string;

    public function getMaxLockTime(): float;

    public function getTimezone(): string;

    public function hourly(): self;

    /**
     * @param int|int[] $offset
     */
    public function hourlyAt(int|array $offset): self;

    public function isDue(): bool;

    public function mondays(): self;

    public function monthly(): self;

    public function monthlyOn(?int $day = null, ?string $time = null): self;

    public function quarterly(): self;

    public function run(Application $app): void;

    public function saturdays(): self;

    public function setAllowOverlapping(?bool $allowOverlapping = null): self;

    public function setMaxLockTime(float $seconds): self;

    public function setTimezone(DateTimeZone|string $timezone): self;

    public function skip(callable|bool $callback): self;

    public function spliceIntoPosition(int $position, int|string $value): self;

    public function sundays(): self;

    public function then(callable $func): self;

    public function thursdays(): self;

    public function tuesdays(): self;

    public function twiceDaily(?int $first = null, ?int $second = null): self;

    public function twiceMonthly(?int $first = null, ?int $second = null): self;

    public function unlessBetween(string $startTime, string $endTime): self;

    public function wednesdays(): self;

    public function weekdays(): self;

    public function weekends(): self;

    public function weekly(): self;

    public function weeklyOn(int $day, ?string $time = null): self;

    public function when(callable|bool $callback): self;

    public function yearly(): self;
}
