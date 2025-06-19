<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Queues\Sqs\Jobs;

use Bref\LaravelBridge\Queue\SqsJob;
use Throwable;

final class SqsQueueJob extends SqsJob
{
    private ?Throwable $throwable = null;

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
}
