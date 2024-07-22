<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Entry;

use Carbon\Carbon;
use Closure;
use Cron\CronExpression;
use DateTimeZone;

abstract class AbstractScheduleEntry implements ScheduleEntryInterface
{
    private string $expression = '* * * * *';

    /**
     * @var callable[]
     */
    private array $filters = [];

    /**
     * @var callable[]
     */
    private array $rejects = [];

    private null|DateTimeZone|string $timezone = null;

    public function at(string $time): ScheduleEntryInterface
    {
        return $this->dailyAt($time);
    }

    public function between(string $startTime, string $endTime): ScheduleEntryInterface
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    public function cron(string $expression): ScheduleEntryInterface
    {
        $this->expression = $expression;

        return $this;
    }

    public function daily(): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    public function dailyAt(string $time): ScheduleEntryInterface
    {
        $segments = \explode(':', $time);

        return $this
            ->spliceIntoPosition(2, (int)$segments[0])
            ->spliceIntoPosition(1, \count($segments) === 2 ? (int)$segments[1] : '0');
    }

    public function days(int|array $days): ScheduleEntryInterface
    {
        $days = \is_array($days) ? $days : [$days];

        return $this->spliceIntoPosition(5, \implode(',', $days));
    }

    public function everyFifteenMinutes(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, '*/15');
    }

    public function everyFiveMinutes(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    public function everyMinute(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, '*');
    }

    public function everyTenMinutes(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    public function everyThirtyMinutes(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    public function filtersPass(): bool
    {
        foreach ($this->filters as $filter) {
            if ((bool)$filter() === false) {
                return false;
            }
        }

        foreach ($this->rejects as $reject) {
            if ((bool)$reject() === true) {
                return false;
            }
        }

        return true;
    }

    public function fridays(): ScheduleEntryInterface
    {
        return $this->days(5);
    }

    public function getCronExpression(): string
    {
        return $this->expression;
    }

    public function getTimezone(): string
    {
        return Carbon::now($this->timezone)->getTimezone()->getName();
    }

    public function hourly(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * @param int|int[] $offset
     */
    public function hourlyAt(int|array $offset): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(1, \is_array($offset) ? \implode(',', $offset) : $offset);
    }

    public function mondays(): ScheduleEntryInterface
    {
        return $this->days(1);
    }

    public function monthly(): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    public function monthlyOn(?int $day = null, ?string $time = null): ScheduleEntryInterface
    {
        $this->dailyAt($time ?? '0:0');

        return $this->spliceIntoPosition(3, $day ?? 1);
    }

    public function quarterly(): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '1-12/3');
    }

    public function saturdays(): ScheduleEntryInterface
    {
        return $this->days(6);
    }

    public function setTimezone(DateTimeZone|string $timezone): ScheduleEntryInterface
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function skip(callable|bool $callback): ScheduleEntryInterface
    {
        $this->rejects[] = \is_callable($callback)
            ? $callback
            : static fn (): bool => $callback;

        return $this;
    }

    public function spliceIntoPosition(int $position, int|string $value): ScheduleEntryInterface
    {
        $segments = \explode(' ', $this->expression);

        $segments[$position - 1] = $value;

        return $this->cron(\implode(' ', $segments));
    }

    public function sundays(): ScheduleEntryInterface
    {
        return $this->days(0);
    }

    public function thursdays(): ScheduleEntryInterface
    {
        return $this->days(4);
    }

    public function tuesdays(): ScheduleEntryInterface
    {
        return $this->days(2);
    }

    public function twiceDaily(?int $first = null, ?int $second = null): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, \sprintf('%d,%d', $first ?? 1, $second ?? 13));
    }

    public function twiceMonthly(?int $first = null, ?int $second = null): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, \sprintf('%d,%d', $first ?? 1, $second ?? 16));
    }

    public function unlessBetween(string $startTime, string $endTime): ScheduleEntryInterface
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    public function wednesdays(): ScheduleEntryInterface
    {
        return $this->days(3);
    }

    public function weekdays(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    public function weekends(): ScheduleEntryInterface
    {
        return $this->spliceIntoPosition(5, '0,6');
    }

    public function weekly(): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    public function weeklyOn(int $day, ?string $time = null): ScheduleEntryInterface
    {
        $this->dailyAt($time ?? '0:0');

        return $this->spliceIntoPosition(5, $day);
    }

    public function when(callable|bool $callback): ScheduleEntryInterface
    {
        $this->filters[] = \is_callable($callback)
            ? $callback
            : static fn (): bool => $callback;

        return $this;
    }

    public function yearly(): ScheduleEntryInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    protected function expressionPasses(): bool
    {
        $date = Carbon::now();

        if ($this->timezone !== null) {
            $date->setTimezone($this->timezone);
        }

        return (new CronExpression($this->expression))->isDue($date->toDateTimeString());
    }

    protected function getExpression(): string
    {
        return $this->expression;
    }

    private function inTimeInterval(string $startTime, string $endTime): Closure
    {
        return fn (): bool => Carbon::now($this->timezone)->between(
            Carbon::parse($startTime, $this->timezone),
            Carbon::parse($endTime, $this->timezone),
            true
        );
    }
}
