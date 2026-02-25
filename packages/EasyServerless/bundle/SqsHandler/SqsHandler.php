<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\SqsHandler;

use AsyncAws\Sqs\SqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsRecord;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use EonX\EasyServerless\Messenger\SqsHandler\AbstractSqsHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsReceivedStamp;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsXrayTraceHeaderStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\RecoverableExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Throwable;

final class SqsHandler extends AbstractSqsHandler
{
    private const SYMFONY_HEADERS_ATTRIBUTE_NAME = 'X-Symfony-Messenger';

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly SerializerInterface $serializer,
        #[Autowire(service: 'messenger.retry_strategy_locator')]
        private readonly ContainerInterface $retryStrategyLocator,
        private readonly SqsClient $sqsClient,
        protected ?LoggerInterface $logger = null,
        private readonly ?ErrorHandlerInterface $errorHandler = null,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        private readonly string $transportName = 'async',
        int $appMaxRetries = 3,
        bool $partialBatchFailure = false,
        int $timeoutThresholdMilliseconds = 1000,
    ) {
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
        try {
            $envelope = $this->serializer->decode([
                'body' => $sqsRecord->getBody(),
                'headers' => $this->resolveHeaders($sqsRecord),
            ]);
        } catch (Throwable $throwable) {
            $this->errorHandler?->report($throwable);
            $this->logger?->error('Error while decoding message into Envelope', [
                'error' => $throwable->getMessage(),
            ]);

            // The rest of the logic relies on having a valid Envelope, even during error handling,
            // so we skip processing this record further, and will not schedule it for retry as it is
            // most likely the application will not be able to decode the message ever
            return;
        }

        try {
            $stamps = [
                new AmazonSqsReceivedStamp($sqsRecord->getMessageId()),
                new ConsumedByWorkerStamp(),
                new ReceivedStamp($this->transportName),
                new TransportMessageIdStamp($sqsRecord->getMessageId()),
            ];

            if ($sqsRecord->getApproximateReceiveCount() > 1) {
                $stamps[] = new RedeliveryStamp($sqsRecord->getApproximateReceiveCount());
            }

            if ($context->getTraceId() !== '') {
                $stamps[] = new AmazonSqsXrayTraceHeaderStamp($context->getTraceId());
            }

            // Replacing the envelope with the containing the new stamps allows the retry strategy to work properly
            $envelope = $envelope->with(...$stamps);

            $event = new WorkerMessageReceivedEvent($envelope, $this->transportName);
            $this->eventDispatcher?->dispatch($event);
            $envelope = $event->getEnvelope();

            if ($event->shouldHandle() === false) {
                return;
            }

            $envelope = $this->bus->dispatch($envelope);

            $this->logger?->info('{class} was handled successfully.', [
                'class' => $envelope->getMessage()::class,
                'message_id' => $envelope->last(TransportMessageIdStamp::class)?->getId(),
                'transport' => $this->transportName,
            ]);
        } catch (Throwable $throwable) {
            $retryStrategy = $this->getRetryStrategyForTransport($this->transportName);
            $isThrowableExplicitlyUnrecoverable = $this->isThrowableExplicitlyUnRecoverable($throwable);
            $shouldRetry = $isThrowableExplicitlyUnrecoverable === false
                && $retryStrategy && $retryStrategy->isRetryable($envelope, $throwable);

            // As explained in parent::shouldSkipRecord(), in some scenarios we must requeue messages even when
            // the application will not retry them so they can end up in the DLQ if configured,
            // except when the application is explicitly preventing retries using an Unrecoverable throwable
            $shouldRequeue = $isThrowableExplicitlyUnrecoverable
                && $sqsRecord->getApproximateReceiveCount() >= $this->appMaxRetries;

            if ($shouldRetry === false) {
                $this->logger?->error(\sprintf(
                    'SQS Record with MessageId "%s" failed to process but will not be retried%s',
                    $sqsRecord->getMessageId(),
                    $isThrowableExplicitlyUnrecoverable ? ' - explicitly marked as unrecoverable' : ''
                ));
            }

            $this->errorHandler?->report(RetryableException::fromThrowable($throwable, $shouldRetry));

            // SQS built-in retry mechanism uses the list of failed messages returned by the Lambda function,
            // this is why we mark the record as failed only if we want it to be retried by SQS.
            // Failure reports should be handled by the application itself (e.g. logging, error tracking, etc.)
            if ($shouldRetry || $shouldRequeue) {
                // As identified during experimenting, this is not ideal as by default Lambda gets a batch of records
                // and if one fails, all are retried creating side effects of reprocessing successful ones.
                // It is highly recommended to enable partial batch failure, but still support not having it enabled
                if ($this->partialBatchFailure === false) {
                    throw $this->resolveOriginalThrowable($throwable);
                }

                if ($shouldRequeue) {
                    $this->scheduleForRetry($sqsRecord);

                    return;
                }

                // We want to add a delay only if we are actually retrying the message
                // We consider a message as failed only if we are not triggering this logic because of a requeue
                $this->scheduleForRetry(
                    sqsRecord: $sqsRecord,
                    retryDelaySeconds: $this->resolveRetryDelay($throwable, $envelope, $retryStrategy),
                    forFailure: true
                );
            }
        } finally {
            $this->eventDispatcher?->dispatch(new EnvelopeDispatchedEvent());
        }
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getRetryStrategyForTransport(string $alias): ?RetryStrategyInterface
    {
        if ($this->retryStrategyLocator->has($alias)) {
            $retryStrategy = $this->retryStrategyLocator->get($alias);

            return $retryStrategy instanceof RetryStrategyInterface ? $retryStrategy : null;
        }

        return null;
    }

    private function isThrowableExplicitlyUnRecoverable(Throwable $throwable): bool
    {
        if ($throwable instanceof RecoverableExceptionInterface) {
            return false;
        }

        // If one or more nested Exceptions is an instance of RecoverableExceptionInterface we should retry
        // If ALL nested Exceptions are an instance of UnrecoverableExceptionInterface we should not retry
        if ($throwable instanceof HandlerFailedException) {
            $shouldNotRetry = true;

            foreach ($throwable->getWrappedExceptions() as $nestedException) {
                if ($nestedException instanceof RecoverableExceptionInterface) {
                    return false;
                }

                if ($nestedException instanceof UnrecoverableExceptionInterface === false) {
                    $shouldNotRetry = false;

                    break;
                }
            }

            if ($shouldNotRetry) {
                return true;
            }
        }

        if ($throwable instanceof UnrecoverableExceptionInterface) {
            return true;
        }

        return false;
    }

    private function resolveHeaders(SqsRecord $record): array
    {
        $headers = [];
        $attributes = $record->getMessageAttributes();

        if (($attributes[self::SYMFONY_HEADERS_ATTRIBUTE_NAME]['dataType'] ?? null) === 'String') {
            /** @var array $headers */
            $headers = \json_decode($attributes[self::SYMFONY_HEADERS_ATTRIBUTE_NAME]['stringValue'] ?? '{}', true);

            unset($attributes[self::SYMFONY_HEADERS_ATTRIBUTE_NAME]);
        }

        foreach ($attributes as $name => $attribute) {
            if (($attribute['dataType'] ?? null) === 'String') {
                $headers[$name] = $attribute['stringValue'] ?? '';
            }
        }

        return $headers;
    }

    private function resolveOriginalThrowable(Throwable $throwable): Throwable
    {
        while ($throwable instanceof HandlerFailedException) {
            $throwable = $throwable->getPrevious() ?? $throwable;
        }

        return $throwable;
    }

    private function resolveRetryDelay(
        Throwable $throwable,
        Envelope $envelope,
        RetryStrategyInterface $retryStrategy,
    ): int {
        $delayInMilliseconds = null;

        if ($throwable instanceof RecoverableExceptionInterface && \method_exists($throwable, 'getRetryDelay')) {
            $delayInMilliseconds = $throwable->getRetryDelay();
        }

        if ($throwable instanceof HandlerFailedException) {
            foreach ($throwable->getWrappedExceptions() as $nestedException) {
                if ($nestedException instanceof RecoverableExceptionInterface === false
                    || \method_exists($nestedException, 'getRetryDelay') === false
                    || 0 > $retryDelay = $nestedException->getRetryDelay() ?? -1
                ) {
                    continue;
                }

                if ($retryDelay < ($delayInMilliseconds ?? \PHP_INT_MAX)) {
                    $delayInMilliseconds = $retryDelay;
                }
            }
        }

        $delayInMilliseconds ??= $retryStrategy->getWaitingTime($envelope, $throwable);
        $delay = (int)\ceil($delayInMilliseconds / 1000);

        // Ensure a minimum delay to ensure the Lambda function has time to complete before the message
        // becomes visible again, and prevent to exceed SQS limit
        return \min(\max($delay, self::DEFAULT_RETRY_DELAY_SECONDS), self::MAX_RETRY_DELAY_SECONDS);
    }
}
