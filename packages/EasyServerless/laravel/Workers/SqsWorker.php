<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Workers;

use Bref\LaravelBridge\Queue\Worker;
use EonX\EasyServerless\Laravel\Jobs\SqsQueueJob;
use Illuminate\Queue\WorkerOptions;
use Throwable;

final class SqsWorker extends Worker
{
    /**
     * @param string $connectionName
     * @param \Illuminate\Contracts\Queue\Job $job
     *
     * @throws \Throwable
     */
    protected function handleJobException($connectionName, $job, WorkerOptions $options, Throwable $e): void
    {
        // Laravel marks a job as failed only when it exceeds its max attempts, otherwise it releases it.
        // Keep the exception so the SQS handler can surface it for released jobs as well
        if ($job instanceof SqsQueueJob) {
            $job->setThrowable($e);
        }

        parent::handleJobException($connectionName, $job, $options, $e);
    }
}
