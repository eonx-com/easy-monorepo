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
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\SqsQueue;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Facade;
use RuntimeException;
use Throwable;

final class SqsHandler extends AbstractSqsHandler
{
    private const RETRY_STOP_REASON_JOB_FAILED = 'job_failed';

    private const RETRY_STOP_REASON_JOB_MAX_TRIES = 'job_max_tries';

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
        iterable $stateCheckers = [],
    ) {
        $queue = $this->container->make(QueueManager::class)
            ->connection($this->connectionName);

        if ($queue instanceof SqsQueue === false) {
            throw new RuntimeException('Default queue connection is not a SQS connection');
        }

        $this->logger = $this->container->make('log');
        $this->sqsClient = $queue->getSqs();
        $this->worker = $this->makeWorker();

        parent::__construct($appMaxRetries, $partialBatchFailure, $timeoutThresholdMilliseconds, $stateCheckers);
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

        // SQS keeps no state about why a message was requeued, only how many times it was received.
        // When the job failed on its last attempt before appMaxRetries was reached, the message was requeued
        // so it can reach the DLQ, and must not be executed (and failed) again
        if ($job->attempts() > $job->maxTries()) {
            $this->logger?->debug(
                \sprintf(
                    'Skipping MessageId "%s" because it has reached the job max tries (%d)',
                    $sqsRecord->getMessageId(),
                    $job->maxTries()
                )
            );

            $this->scheduleForRetry($sqsRecord);

            return;
        }

        $this->worker->runSqsJob($job, $this->connectionName, $workerOptions);

        // The worker released the job, it will be retried after the backoff of the job
        if ($job->isReleased() && $job->hasFailed() === false && $job->attempts() < $job->maxTries()) {
            if ($this->partialBatchFailure === false) {
                throw new RuntimeException(\sprintf('Job "%s" failed and will be retried', $job->getJobId()));
            }

            $this->scheduleForRetry(
                sqsRecord: $sqsRecord,
                retryDelaySeconds: $this->resolveRetryDelay($job),
                forFailure: true
            );

            return;
        }

        // The job released itself on its last attempt, the worker would fail it on the next attempt
        if ($job->isReleased() && $job->hasFailed() === false) {
            $this->failJob($job);
        }

        if ($job->hasFailed() === false) {
            return;
        }

        $isJobExplicitlyUnrecoverable = $job->isExplicitlyUnrecoverable();

        // The worker failed the job before its last attempt, e.g. $this->fail() in the job, FailOnException,
        // $maxExceptions or retryUntil. It cannot be requeued for the DLQ, as it would be executed again
        // on the next attempt
        $hasJobFailedBeforeLastAttempt = $isJobExplicitlyUnrecoverable === false
            && $job->attempts() < $job->maxTries();
        $shouldRequeue = $isJobExplicitlyUnrecoverable === false && $hasJobFailedBeforeLastAttempt === false;

        // The tries of the job are known only at runtime, from the message payload
        if ($shouldRequeue && $job->isMaxTriesLimitedByAppMaxRetries()) {
            $this->logger?->warning(
                \sprintf(
                    'Job "%s" allows %s tries, but the SQS handler allows only %d attempts (appMaxRetries)',
                    $job->resolveName(),
                    $job->getRawMaxTries() === 0 ? 'unlimited' : $job->getRawMaxTries(),
                    $this->appMaxRetries
                ),
                [
                    'message_id' => $sqsRecord->getMessageId(),
                ]
            );
        }

        $this->logger?->error(
            \sprintf(
                'SQS Record failed to process but will not be retried%s',
                match (true) {
                    $isJobExplicitlyUnrecoverable => ' - explicitly marked as unrecoverable',
                    $hasJobFailedBeforeLastAttempt => ' - failed by the job before its last attempt',
                    default => '',
                }
            ),
            [
                'app_max_retries' => $this->appMaxRetries,
                'attempt' => $job->attempts(),
                'message_id' => $sqsRecord->getMessageId(),
                'retry_stop_reason' => $this->resolveRetryStopReason(
                    $job,
                    $isJobExplicitlyUnrecoverable,
                    $hasJobFailedBeforeLastAttempt
                ),
            ]
        );

        // SQS built-in retry mechanism uses the list of failed messages returned by the Lambda function,
        // this is why we mark the record as failed only if we want it to be retried or requeued by SQS.
        // As explained in parent::shouldSkipRecord(), we requeue messages the application will not retry
        // so they can end up in the DLQ if configured, except when the job is explicitly unrecoverable
        if ($shouldRequeue === false) {
            return;
        }

        // As identified during experimenting, this is not ideal as by default Lambda gets a batch of records
        // and if one fails, all are retried creating side effects of reprocessing successful ones.
        // It is highly recommended to enable partial batch failure, but still support not having it enabled
        if ($this->partialBatchFailure === false) {
            throw $job->getThrowable() ?? new RuntimeException('Job failed without an exception');
        }

        $this->scheduleForRetry($sqsRecord);
    }

    private function calculateJobTimeout(int $remainingInvocationTimeInMs): int
    {
        return \max((int)(($remainingInvocationTimeInMs - self::SAFETY_TIMEOUT_MARGIN_MILLISECONDS) / 1000), 0);
    }

    /**
     * The worker fails jobs within its own error handling, the same is needed here so an error in the failed()
     * method of the job or in a JobFailed listener does not fail the whole Lambda invocation.
     */
    private function failJob(SqsQueueJob $job): void
    {
        try {
            $job->fail(MaxAttemptsExceededException::forJob($job));
        } catch (Throwable $throwable) {
            $this->logger?->error('Error while failing the job', [
                'error' => $throwable->getMessage(),
                'message_id' => $job->getJobId(),
            ]);
        }
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
            appMaxRetries: $this->appMaxRetries,
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

    private function resolveRetryDelay(SqsQueueJob $job): int
    {
        // Ensure a minimum delay to ensure the Lambda function has time to complete before the message
        // becomes visible again, and prevent to exceed SQS limit
        return \min(
            \max($job->getReleaseDelay() ?? 0, self::DEFAULT_RETRY_DELAY_SECONDS),
            self::MAX_RETRY_DELAY_SECONDS
        );
    }

    private function resolveRetryStopReason(
        SqsQueueJob $job,
        bool $isJobExplicitlyUnrecoverable,
        bool $hasJobFailedBeforeLastAttempt,
    ): string {
        if ($isJobExplicitlyUnrecoverable) {
            return self::RETRY_STOP_REASON_UNRECOVERABLE;
        }

        if ($hasJobFailedBeforeLastAttempt) {
            return self::RETRY_STOP_REASON_JOB_FAILED;
        }

        // When the tries of the job and appMaxRetries are equal, the tries of the job are reported
        return $job->getRawMaxTries() !== null && $job->isMaxTriesLimitedByAppMaxRetries() === false
            ? self::RETRY_STOP_REASON_JOB_MAX_TRIES
            : self::RETRY_STOP_REASON_APP_MAX_RETRIES;
    }
}
