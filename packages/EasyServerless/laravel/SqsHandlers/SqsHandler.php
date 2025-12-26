<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\SqsHandlers;

use Aws\Sqs\SqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsHandler as BaseSqsHandler;
use Bref\Event\Sqs\SqsRecord;
use Bref\LaravelBridge\MaintenanceMode;
use Bref\LaravelBridge\Queue\Worker;
use EonX\EasyServerless\Laravel\Jobs\SqsQueueJob;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Log\LogManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\SqsQueue;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * The purpose of this class and related SqsQueueJob is to handle SQS events in a way that delegates retries to SQS
 * itself, allowing us to fully utilize its retries and dead-letter queue capabilities.
 * It also allows us to handle partial batch failures, which is useful when processing multiple messages in a single
 * batch.
 */
final class SqsHandler extends BaseSqsHandler
{
    private const float JOB_TIMEOUT_SAFETY_MARGIN = 1.0;

    private readonly LogManager $logger;

    private readonly SqsClient $sqsClient;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        private readonly Container $container,
        private readonly string $connectionName = 'sqs',
        private readonly bool $partialBatchFailure = false,
    ) {
        $queue = $this->container->make(QueueManager::class)
            ->connection($this->connectionName);

        if ($queue instanceof SqsQueue === false) {
            throw new RuntimeException('Default queue connection is not a SQS connection');
        }

        $this->logger = $this->container->make('log');
        $this->sqsClient = $queue->getSqs();
    }

    /**
     * @throws \Bref\Event\InvalidLambdaEvent
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function handleSqs(SqsEvent $event, Context $context): void
    {
        $worker = $this->makeWorker();

        $isFifo = null;
        $hasPreviousMessageFailed = false;
        $failingMessageGroupIds = [];

        foreach ($event->getRecords() as $sqsRecord) {
            $isFifo ??= \str_ends_with((string)$sqsRecord->toArray()['eventSourceARN'], '.fifo');
            $messageGroupId = $sqsRecord->toArray()['attributes']['MessageGroupId'] ?? null;

            /**
             * When using FIFO queues, preserving order is important.
             * If a previous message has failed in the batch, we need to skip the next ones and requeue them.
             */
            if ($isFifo && $hasPreviousMessageFailed && \in_array($messageGroupId, $failingMessageGroupIds, true)) {
                $this->markAsFailed($sqsRecord);

                continue;
            }

            $timeout = $this->calculateJobTimeout($context->getRemainingTimeInMillis());
            $job = $this->makeSqsQueueJob($sqsRecord);
            $workerOptions = $this->makeWorkerOptions($timeout);

            $worker->runSqsJob($job, $this->connectionName, $workerOptions);

            if ($job->hasFailed()) {
                // If the job explicitly prevents retries, then let lambda acknowledge it
                if ($job->maxTries() === 1) {
                    $this->logger->error(\sprintf($this->getUnrecoverableJobMessage(), $sqsRecord->getMessageId()));

                    continue;
                }

                if ($this->partialBatchFailure === false) {
                    throw $job->getThrowable() ?? new RuntimeException('Job failed without an exception');
                }

                $failingMessageGroupIds[] = $messageGroupId;
                $hasPreviousMessageFailed = true;

                $this->markAsFailed($sqsRecord);
            }
        }
    }

    private function calculateJobTimeout(int $remainingInvocationTimeInMs): int
    {
        return \max((int)(($remainingInvocationTimeInMs - self::JOB_TIMEOUT_SAFETY_MARGIN) / 1000), 0);
    }

    private function getUnrecoverableJobMessage(): string
    {
        return 'SQS record with id "%s" failed to be processed. But Job::$tries was set to 1 not to re-attempt.'
            . ' Message will be acknowledged';
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
            maxTries: 50, // Set high on purpose as we delegate retries to SQS
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
        if (\method_exists($this->logger, 'flushSharedContext')) {
            $this->logger->flushSharedContext();
        }

        if (\method_exists($this->logger, 'withoutContext')) {
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
