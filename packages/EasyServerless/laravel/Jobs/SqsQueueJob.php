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
        private readonly int $appMaxRetries,
    ) {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);
    }

    /**
     * The message is never sent again to the queue, SQS retries the same message.
     * The number of attempts is the number of times SQS delivered it.
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
        $this->releaseDelay = (int)$this->secondsUntil($delay);
    }
}
