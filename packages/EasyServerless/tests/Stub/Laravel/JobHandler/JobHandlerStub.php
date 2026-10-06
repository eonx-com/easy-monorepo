<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Laravel\JobHandler;

use Illuminate\Contracts\Queue\Job;
use Throwable;

final class JobHandlerStub
{
    private int $failedCount = 0;

    private int $firedCount = 0;

    public function __construct(
        private readonly ?Throwable $throwable = null,
        private readonly bool $failManually = false,
        private readonly ?int $releaseDelay = null,
        private readonly ?Throwable $failedThrowable = null,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function failed(array $data, ?Throwable $throwable): void
    {
        $this->failedCount++;

        if ($this->failedThrowable !== null) {
            throw $this->failedThrowable;
        }
    }

    /**
     * @throws \Throwable
     */
    public function fire(Job $job, array $data): void
    {
        $this->firedCount++;

        if ($this->failManually) {
            $job->fail($this->throwable);

            return;
        }

        if ($this->releaseDelay !== null) {
            $job->release($this->releaseDelay);

            return;
        }

        if ($this->throwable !== null) {
            throw $this->throwable;
        }
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function getFiredCount(): int
    {
        return $this->firedCount;
    }
}
