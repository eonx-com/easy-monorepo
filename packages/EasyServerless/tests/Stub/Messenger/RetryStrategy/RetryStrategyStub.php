<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Messenger\RetryStrategy;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Throwable;

/**
 * Same formula as MultiplierRetryStrategy without jitter, which cannot be disabled with the lowest
 * supported symfony/messenger version.
 */
final readonly class RetryStrategyStub implements RetryStrategyInterface
{
    public function __construct(
        private int $maxRetries,
        private int $delayMilliseconds,
        private int $multiplier,
    ) {
    }

    public function getWaitingTime(Envelope $message, ?Throwable $throwable = null): int
    {
        return $this->delayMilliseconds * $this->multiplier ** RedeliveryStamp::getRetryCountFromEnvelope($message);
    }

    public function isRetryable(Envelope $message, ?Throwable $throwable = null): bool
    {
        return RedeliveryStamp::getRetryCountFromEnvelope($message) < $this->maxRetries;
    }
}
