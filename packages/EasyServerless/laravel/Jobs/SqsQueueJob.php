<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Jobs;

use Bref\LaravelBridge\Queue\SqsJob;
use Throwable;

final class SqsQueueJob extends SqsJob
{
    private ?Throwable $throwable = null;

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

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    public function release($delay = 0): void
    {
        // DO NOT release the job, just mark it as released
        // Releasing the job prevents SQS from retrying it
        $this->released = true;
    }

    public function setThrowable(?Throwable $throwable = null): void
    {
        $this->throwable = $throwable;
    }
}
