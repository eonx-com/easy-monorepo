<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Jobs;

use Aws\Sqs\SqsClient;
use Bref\LaravelBridge\Queue\SqsJob;
use Illuminate\Container\Container;
use Throwable;

final class SqsQueueJob extends SqsJob
{
    private ?int $releaseDelay = null;

    private ?Throwable $throwable = null;

    public function __construct(
        Container $container,
        SqsClient $sqs,
        array $job,
        string $connectionName,
        string $queue,
        private readonly int $appMaxRetries = 3,
    ) {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);
    }

    /**
     * Bref counts attempts from the payload, which is only incremented when it re-sends the message on release.
     * Since release is disabled to let SQS retry the message itself, the received count is the source of truth.
     */
    public function attempts(): int
    {
        return (int)($this->job['Attributes']['ApproximateReceiveCount'] ?? 1);
    }

    public function delete(): void
    {
        // DO NOT delete the job, just mark it as deleted
        // Deleting the job prevents SQS from retrying it
        $this->deleted = true;
    }

    public function fail($e = null): void
    {
        $this->throwable = $e;

        parent::fail($e);
    }

    /**
     * The tries of the job as set in the message payload.
     */
    public function getRawMaxTries(): ?int
    {
        $maxTries = parent::maxTries();

        return \is_int($maxTries) ? $maxTries : null;
    }

    public function getReleaseDelay(): ?int
    {
        return $this->releaseDelay;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * The application can explicitly prevent retries by setting maxTries to 1 on the job.
     */
    public function isExplicitlyUnrecoverable(): bool
    {
        return $this->getRawMaxTries() === 1;
    }

    /**
     * True when the job allows more tries than appMaxRetries (0 means unlimited tries in Laravel).
     */
    public function isMaxTriesLimitedByAppMaxRetries(): bool
    {
        $maxTries = $this->getRawMaxTries();

        return $maxTries !== null && ($maxTries === 0 || $maxTries > $this->appMaxRetries);
    }

    /**
     * The handler cannot execute a job more than appMaxRetries times,
     * the tries of the job can only lower this limit.
     */
    public function maxTries(): int
    {
        $maxTries = $this->getRawMaxTries();

        return $maxTries !== null && $maxTries > 0 && $maxTries < $this->appMaxRetries
            ? $maxTries
            : $this->appMaxRetries;
    }

    public function release($delay = 0): void
    {
        // DO NOT release the job, just mark it as released
        // Releasing the job prevents SQS from retrying it
        $this->released = true;
        // Keep the delay, either explicit or computed by the worker from the backoff of the job,
        // so the handler can apply it to the SQS message visibility
        $this->releaseDelay = (int)$this->secondsUntil($delay);
    }

    public function setThrowable(?Throwable $throwable = null): void
    {
        $this->throwable = $throwable;
    }
}
