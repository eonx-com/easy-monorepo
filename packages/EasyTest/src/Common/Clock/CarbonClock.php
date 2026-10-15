<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Clock;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Symfony\Component\Clock\ClockInterface;

/**
 * Symfony clock reading the global Carbon time: when Carbon test time is set, sleeping moves it instead of sleeping,
 * so Carbon::now() follows every clock move. Each instance, including the ones from withTimeZone(), shares that time.
 */
final readonly class CarbonClock implements ClockInterface
{
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {}

    public function now(): CarbonImmutable
    {
        $now = CarbonImmutable::now();

        return $this->timezone === null ? $now : $now->setTimezone($this->timezone);
    }

    public function sleep(float|int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        CarbonImmutable::sleep($seconds);
    }

    public function withTimeZone(DateTimeZone|string $timezone): static
    {
        return new self(\is_string($timezone) ? new DateTimeZone($timezone) : $timezone);
    }
}
