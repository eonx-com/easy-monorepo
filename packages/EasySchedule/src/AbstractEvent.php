<?php

declare(strict_types=1);

namespace EonX\EasySchedule;

use Carbon\Carbon;
use Cron\CronExpression;
use EonX\EasySchedule\Interfaces\EventInterface;

abstract class AbstractEvent implements EventInterface
{
    /**
     * @var string
     */
    private $expression = '* * * * *';

    /**
     * @var callable[]
     */
    private $filters = [];

    /**
     * @var callable[]
     */
    private $rejects = [];

    /**
     * @var \DateTimeZone|string
     */
    private $timezone;

    public function at(string $time): EventInterface
    {
        return $this->dailyAt($time);
    }

    public function between(string $startTime, string $endTime): EventInterface
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    public function cron(string $expression): EventInterface
    {
        $this->expression = $expression;

        return $this;
    }

    public function daily(): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    public function dailyAt(string $time): EventInterface
    {
        $segments = \explode(':', $time);

        return $this
            ->spliceIntoPosition(2, (int)$segments[0])
            ->spliceIntoPosition(1, \count($segments) === 2 ? (int)$segments[1] : '0');
    }

    /**
     * @param mixed $days
     */
    public function days($days): EventInterface
    {
        $days = \is_array($days) ? $days : \func_get_args();

        return $this->spliceIntoPosition(5, \implode(',', $days));
    }

    public function everyFifteenMinutes(): EventInterface
    {
        return $this->spliceIntoPosition(1, '*/15');
    }

    public function everyFiveMinutes(): EventInterface
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    public function everyMinute(): EventInterface
    {
        return $this->spliceIntoPosition(1, '*');
    }

    public function everyTenMinutes(): EventInterface
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    public function everyThirtyMinutes(): EventInterface
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    public function filtersPass(): bool
    {
        foreach ($this->filters as $filter) {
            if ((bool)\call_user_func($filter) === false) {
                return false;
            }
        }

        foreach ($this->rejects as $reject) {
            if ((bool)\call_user_func($reject) === true) {
                return false;
            }
        }

        return true;
    }

    public function fridays(): EventInterface
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

    public function hourly(): EventInterface
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * @param int|int[] $offset
     */
    public function hourlyAt($offset): EventInterface
    {
        return $this->spliceIntoPosition(
            1,
            \is_array($offset) ? \implode(',', $offset) : $offset
        );
    }

    public function mondays(): EventInterface
    {
        return $this->days(1);
    }

    public function monthly(): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    public function monthlyOn(?int $day = null, ?string $time = null): EventInterface
    {
        $this->dailyAt($time ?? '0:0');

        return $this->spliceIntoPosition(3, $day ?? 1);
    }

    public function quarterly(): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '1-12/3');
    }

    public function saturdays(): EventInterface
    {
        return $this->days(6);
    }

    /**
     * @param \DateTimeZone|string $timezone
     */
    public function setTimezone($timezone): EventInterface
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @param callable|bool $callback
     */
    public function skip($callback): EventInterface
    {
        $this->rejects[] = \is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    /**
     * @param int|string $value
     */
    public function spliceIntoPosition(int $position, $value): EventInterface
    {
        $segments = \explode(' ', $this->expression);

        $segments[$position - 1] = $value;

        return $this->cron(\implode(' ', $segments));
    }

    public function sundays(): EventInterface
    {
        return $this->days(0);
    }

    public function thursdays(): EventInterface
    {
        return $this->days(4);
    }

    public function tuesdays(): EventInterface
    {
        return $this->days(2);
    }

    public function twiceDaily(?int $first = null, ?int $second = null): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, \sprintf('%d,%d', $first ?? 1, $second ?? 13));
    }

    public function twiceMonthly(?int $first = null, ?int $second = null): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, \sprintf('%d,%d', $first ?? 1, $second ?? 16));
    }

    public function unlessBetween(string $startTime, string $endTime): EventInterface
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    public function wednesdays(): EventInterface
    {
        return $this->days(3);
    }

    public function weekdays(): EventInterface
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    public function weekends(): EventInterface
    {
        return $this->spliceIntoPosition(5, '0,6');
    }

    public function weekly(): EventInterface
    {
        return $this
            ->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    public function weeklyOn(int $day, ?string $time = null): EventInterface
    {
        $this->dailyAt($time ?? '0:0');

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * @param callable|bool $callback
     */
    public function when($callback): EventInterface
    {
        $this->filters[] = \is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    public function yearly(): EventInterface
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

        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }

        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }

    protected function getExpression(): string
    {
        return $this->expression;
    }

    private function inTimeInterval(string $startTime, string $endTime): \Closure
    {
        return function () use ($startTime, $endTime) {
            return Carbon::now($this->timezone)->between(
                Carbon::parse($startTime, $this->timezone),
                Carbon::parse($endTime, $this->timezone),
                true
            );
        };
    }
}
