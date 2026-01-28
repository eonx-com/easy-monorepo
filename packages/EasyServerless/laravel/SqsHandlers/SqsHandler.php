<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\SqsHandlers;

use Aws\Sqs\SqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsRecord;
use Bref\LaravelBridge\MaintenanceMode;
use Bref\LaravelBridge\Queue\Worker;
use EonX\EasyServerless\Laravel\Jobs\SqsQueueJob;
use EonX\EasyServerless\Messenger\SqsHandler\AbstractSqsHandler;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\SqsQueue;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

final class SqsHandler extends AbstractSqsHandler
{
    private readonly SqsClient $sqsClient;

    private readonly Worker $worker;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        private readonly Container $container,
        private readonly string $connectionName = 'sqs',
        int $appMaxRetries = 3,
        int $timeoutThresholdMilliseconds = 1000,
        bool $partialBatchFailure = false,
    ) {
        $queue = $this->container->make(QueueManager::class)
            ->connection($this->connectionName);

        if ($queue instanceof SqsQueue === false) {
            throw new RuntimeException('Default queue connection is not a SQS connection');
        }

        $this->logger = $this->container->make('log');
        $this->sqsClient = $queue->getSqs();
        $this->worker = $this->makeWorker();

        parent::__construct($appMaxRetries, $partialBatchFailure, $timeoutThresholdMilliseconds);
    }

    protected function getSqsClient(): SqsClient
    {
        return $this->sqsClient;
    }

    /**
     * @throws \Throwable
     */
    protected function handleSqsRecords(SqsRecord $sqsRecord, Context $context): void
    {
        $timeout = $this->calculateJobTimeout($context->getRemainingTimeInMillis());
        $job = $this->makeSqsQueueJob($sqsRecord);
        $workerOptions = $this->makeWorkerOptions($timeout);

        $this->worker->runSqsJob($job, $this->connectionName, $workerOptions);

        if ($job->hasFailed()) {
            // The application can explicitly prevent retries by setting maxTries to 1
            $isJobExplicitlyUnrecoverable = $job->maxTries() === 1;
            $shouldRetry = $isJobExplicitlyUnrecoverable === false
                && $sqsRecord->getApproximateReceiveCount() < ($job->maxTries() ?? $this->appMaxRetries);

            // As explained in parent::shouldSkipRecord(), in some scenarios we must requeue messages even when
            // the application will not retry them so they can end up in the DLQ if configured,
            // except when the application is explicitly preventing retries
            $shouldRequeue = $isJobExplicitlyUnrecoverable
                && $sqsRecord->getApproximateReceiveCount() >= $this->appMaxRetries;

            if ($shouldRetry === false) {
                $this->logger?->error(\sprintf(
                    'SQS Record with MessageId "%s" failed to process but will not be retried%s',
                    $sqsRecord->getMessageId(),
                    $isJobExplicitlyUnrecoverable ? ' - explicitly marked as unrecoverable' : ''
                ));
            }

            // SQS built-in retry mechanism uses the list of failed messages returned by the Lambda function,
            // this is why we mark the record as failed only if we want it to be retried by SQS.
            // Failure reports should be handled by the application itself (e.g. logging, error tracking, etc.)
            if ($shouldRetry || $shouldRequeue) {
                // As identified during experimenting, this is not ideal as by default Lambda gets a batch of records
                // and if one fails, all are retried creating side effects of reprocessing successful ones.
                // It is highly recommended to enable partial batch failure, but still support not having it enabled
                if ($this->partialBatchFailure === false) {
                    throw $job->getThrowable() ?? new RuntimeException('Job failed without an exception');
                }

                if ($shouldRequeue) {
                    $this->scheduleForRetry($sqsRecord);

                    return;
                }

                // We want to add a delay only if we are actually retrying the message
                // We consider a message as failed only if we are not triggering this logic because of a requeue
                $this->scheduleForRetry(
                    sqsRecord: $sqsRecord,
                    retryDelaySeconds: \is_int($job->backoff()) ? $job->backoff() : null,
                    forFailure: true
                );
            }
        }
    }

    private function calculateJobTimeout(int $remainingInvocationTimeInMs): int
    {
        return \max((int)(($remainingInvocationTimeInMs - self::SAFETY_TIMEOUT_MARGIN_MILLISECONDS) / 1000), 0);
    }

    private function makeSqsQueueJob(SqsRecord $sqsRecord): SqsQueueJob
    {
        $job = [
            'Attributes' => $sqsRecord->toArray()['attributes'] ?? [],
            'Body' => $sqsRecord->getBody(),
            'MessageAttributes' => $sqsRecord->getMessageAttributes(),
            'MessageId' => $sqsRecord->getMessageId(),
            'ReceiptHandle' => $sqsRecord->getReceiptHandle(),
        ];

        return new SqsQueueJob(
            container: $this->container,
            sqs: $this->sqsClient,
            job: $job,
            connectionName: $this->connectionName,
            queue: $sqsRecord->getQueueName(),
        );
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function makeWorker(): Worker
    {
        $worker = $this->container->make(Worker::class, [
            'isDownForMaintenance' => static fn (): bool => MaintenanceMode::active(),
            'resetScope' => fn () => $this->resetWorkerScope(),
        ]);

        $worker->setCache(
            $this->container->make(Cache::class)
        );

        return $worker;
    }

    private function makeWorkerOptions(int $timeout): WorkerOptions
    {
        return new WorkerOptions(
            name: 'default',
            backoff: 0,
            memory: 512,
            timeout: $timeout,
            sleep: 0,
            maxTries: $this->appMaxRetries,
            force: false,
            stopWhenEmpty: false,
            maxJobs: 0,
            maxTime: 0,
        );
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function resetWorkerScope(): void
    {
        if ($this->logger && \method_exists($this->logger, 'flushSharedContext')) {
            $this->logger->flushSharedContext();
        }

        if ($this->logger && \method_exists($this->logger, 'withoutContext')) {
            $this->logger->withoutContext();
        }

        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = $this->container->make('db');

        if (\method_exists($db, 'getConnections')) {
            foreach ($db->getConnections() as $connection) {
                $connection->resetTotalQueryDuration();
                $connection->allowQueryDurationHandlersToRunAgain();
            }
        }

        $this->container->forgetScopedInstances();

        Facade::clearResolvedInstances();
    }
}
