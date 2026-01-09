<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\SqsHandler;

use AsyncAws\Sqs\SqsClient as AsyncAwsSqsClient;
use Aws\Sqs\SqsClient as AwsSqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsHandler;
use Bref\Event\Sqs\SqsRecord;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractSqsHandler extends SqsHandler
{
    protected const DEFAULT_RETRY_DELAY_SECONDS = 1;

    protected const MAX_RETRY_DELAY_SECONDS = 43200; // 12 hours (SQS limit)

    protected const SAFETY_TIMEOUT_MARGIN_MILLISECONDS = 1000; // 1 second

    protected ?LoggerInterface $logger = null;

    private array $failingMessageGroupIds = [];

    private bool $hasPreviousMessageFailed = false;

    private ?bool $isFifo = null;

    private array $recordsForRetry = [];

    public function __construct(
        protected readonly int $appMaxRetries = 3,
        protected readonly bool $partialBatchFailure = false,
        private readonly int $timeoutThresholdMilliseconds = 1000, // 1 second
    ) {
    }

    /**
     * @throws \Bref\Event\InvalidLambdaEvent
     */
    public function handleSqs(SqsEvent $event, Context $context): void
    {
        $this->reset();

        foreach ($event->getRecords() as $sqsRecord) {
            // In some cases we need to prevent records being processed by the current invocation,
            // we requeue them by scheduling them for retry
            if ($this->shouldSkipRecord($sqsRecord, $context)) {
                $this->scheduleForRetry($sqsRecord);

                continue;
            }

            $this->handleSqsRecords($sqsRecord, $context);
        }

        $this->handleFailedRecords();
    }

    abstract protected function getSqsClient(): AwsSqsClient|AsyncAwsSqsClient;

    abstract protected function handleSqsRecords(SqsRecord $sqsRecord, Context $context): void;

    protected function scheduleForRetry(
        SqsRecord $sqsRecord,
        ?int $retryDelaySeconds = null,
        ?bool $forFailure = null,
    ): void {
        // We keep track of failed records at this level to be able to change their visibility timeout
        // prior to Lambda function completion so they can be retried within the expected delay instead of
        // the default visibility timeout configured on the SQS queue
        $this->recordsForRetry[] = [
            'record' => $sqsRecord,
            'retryDelaySeconds' => $retryDelaySeconds ?? self::DEFAULT_RETRY_DELAY_SECONDS,
        ];

        if ($forFailure ?? false) {
            $this->failingMessageGroupIds[] = $sqsRecord->toArray()['attributes']['MessageGroupId'] ?? null;
            $this->hasPreviousMessageFailed = true;
        }

        $this->markAsFailed($sqsRecord);
    }

    private function handleFailedRecords(): void
    {
        $queueUrl = null;
        $sqsClient = $this->getSqsClient();

        // All records are from the same queue, so we can batch them
        // SQS ChangeMessageVisibilityBatch supports up to 10 messages at a time
        foreach (\array_chunk($this->recordsForRetry, 10) as $recordChunk) {
            $entries = [];

            foreach ($recordChunk as $record) {
                $queueUrl ??= $this->resolveQueueUrl($record['record']);

                $entries[] = [
                    'Id' => $record['record']->getMessageId(),
                    'ReceiptHandle' => $record['record']->getReceiptHandle(),
                    'VisibilityTimeout' => $record['retryDelaySeconds'],
                ];
            }

            try {
                $sqsClient->changeMessageVisibilityBatch([
                    'QueueUrl' => $queueUrl,
                    'Entries' => $entries,
                ]);
            } catch (Throwable $throwable) {
                // Log only not to impact other chunks, as messages will be retried by SQS after visibility timeout
                $this->logger?->warning('Failed to change visibility timeout for failed SQS records', [
                    'error' => $throwable->getMessage(),
                ]);
            }
        }
    }

    private function reset(): void
    {
        $this->failingMessageGroupIds = [];
        $this->hasPreviousMessageFailed = false;
        $this->isFifo = null;
        $this->recordsForRetry = [];
    }

    private function resolveQueueUrl(SqsRecord $record): string
    {
        // QueueARN: arn:aws:sqs:{AWS_REGION}:{AWS_ACCOUNT_ID}:{AWS_SQS_QUEUE_NAME}
        // QueueUrl: https://sqs.{AWS_REGION}.amazonaws.com/{AWS_ACCOUNT_ID}/{AWS_SQS_QUEUE_NAME}

        $queueArn = \explode(':', (string)$record->toArray()['eventSourceARN']);

        return \sprintf(
            'https://sqs.%s.amazonaws.com/%s/%s',
            $queueArn[3],
            $queueArn[4],
            $queueArn[5]
        );
    }

    private function shouldSkipRecord(SqsRecord $sqsRecord, Context $context): bool
    {
        $this->isFifo ??= \str_ends_with((string)$sqsRecord->toArray()['eventSourceARN'], '.fifo');
        $messageGroupId = $sqsRecord->toArray()['attributes']['MessageGroupId'] ?? null;

        // When using FIFO queues, preserving order is important,
        // if a previous message has failed in the batch, we need to skip the next ones and requeue them
        if ($this->isFifo
            && $this->hasPreviousMessageFailed
            && \in_array($messageGroupId, $this->failingMessageGroupIds, true)) {
            $this->logger?->debug(\sprintf(
                'Skipping MessageId "%s" from MessageGroupId "%s" due to previous failure in the same group',
                $sqsRecord->getMessageId(),
                $messageGroupId
            ));

            return true;
        }

        // If the Lambda function about to timeout we skip processing records to prevent partial processing
        $remainingTimeInMilliseconds = $context->getRemainingTimeInMillis() - self::SAFETY_TIMEOUT_MARGIN_MILLISECONDS;
        if ($remainingTimeInMilliseconds <= $this->timeoutThresholdMilliseconds) {
            $this->logger?->debug(\sprintf(
                'Skipping MessageId "%s" because remaining Lambda time (%d ms) is below threshold (%d ms)',
                $sqsRecord->getMessageId(),
                $remainingTimeInMilliseconds,
                $this->timeoutThresholdMilliseconds
            ));

            return true;
        }

        // In the case of a Dead Letter Queue (DLQ) being configured, it becomes the "failed_transport" which
        // keeps failed messages in order to be able to retry them at a later stage using its redrive mechanism
        // SQS puts messages into the DLQ ONLY after they reach the maxReceiveCount configured on the source queue,
        // In the scenario where the application maxRetries is lower than the SQS maxReceiveCount,
        // we need to prevent processing messages that have reached their appMaxRetries while allowing SQS to
        // keep retrying them until they reach maxReceiveCount and are sent to the DLQ
        if ($sqsRecord->getApproximateReceiveCount() > $this->appMaxRetries) {
            $this->logger?->debug(\sprintf(
                'Skipping MessageId "%s" because it has reached the application max retries (%d)',
                $sqsRecord->getMessageId(),
                $this->appMaxRetries
            ));

            return true;
        }

        return false;
    }
}
