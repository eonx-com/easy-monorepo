<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\bundle\SqsHandler;

use AsyncAws\Core\Credentials\Credentials;
use AsyncAws\Sqs\SqsClient;
use Bref\Context\Context;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyServerless\Bundle\SqsHandler\SqsHandler;
use EonX\EasyServerless\Event\ServerlessWorkerMessageFailedEvent;
use EonX\EasyServerless\Tests\Stub\ErrorHandler\ErrorHandler\ErrorHandlerStub;
use EonX\EasyServerless\Tests\Stub\Messenger\MessageBus\MessageBusStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Throwable;

final class SqsHandlerTest extends AbstractUnitTestCase
{
    private const MESSAGE_ID = 'message-id';

    private const TRANSPORT_NAME = 'async';

    /**
     * Retry strategy: delay 10s, multiplier 8, no jitter. Expected waits: 10s, 80s, 640s.
     *
     * @see testHandleFailedMessage
     */
    public static function provideFailedMessageData(): iterable
    {
        // The application allows more attempts than max_retries + 1: the retry strategy decides
        yield 'strategy 3, app 5, attempt 1: retry in 10s' => [1, 3, 5, false, true, 10, false, true];
        yield 'strategy 3, app 5, attempt 2: retry in 80s' => [2, 3, 5, false, true, 80, false, true];
        yield 'strategy 3, app 5, attempt 3: retry in 640s' => [3, 3, 5, false, true, 640, false, true];
        yield 'strategy 3, app 5, attempt 4: last attempt, requeue' => [4, 3, 5, false, true, 1, true, false];
        yield 'strategy 3, app 5, attempt 5: not executed, requeue' => [5, 3, 5, false, false, 1, false, null];
        yield 'strategy 3, app 5, attempt 6: not executed, requeue' => [6, 3, 5, false, false, 1, false, null];

        // The application allows as many attempts as max_retries + 1
        yield 'strategy 3, app 4, attempt 3: retry in 640s' => [3, 3, 4, false, true, 640, false, true];
        yield 'strategy 3, app 4, attempt 4: last attempt, requeue' => [4, 3, 4, false, true, 1, true, false];
        yield 'strategy 3, app 4, attempt 5: not executed, requeue' => [5, 3, 4, false, false, 1, false, null];

        // The application allows fewer attempts than max_retries + 1 (defaults of both)
        yield 'strategy 3, app 3, attempt 1: retry in 10s' => [1, 3, 3, false, true, 10, false, true];
        yield 'strategy 3, app 3, attempt 2: retry in 80s' => [2, 3, 3, false, true, 80, false, true];
        yield 'strategy 3, app 3, attempt 3: last attempt, requeue' => [3, 3, 3, false, true, 1, true, false];
        yield 'strategy 3, app 3, attempt 4: not executed, requeue' => [4, 3, 3, false, false, 1, false, null];

        // Retry strategy gives up before appMaxRetries
        yield 'strategy 1, app 3, attempt 1: retry in 10s' => [1, 1, 3, false, true, 10, false, true];
        yield 'strategy 1, app 3, attempt 2: last attempt, requeue' => [2, 1, 3, false, true, 1, true, false];
        yield 'strategy 1, app 3, attempt 3: not executed, requeue' => [3, 1, 3, false, false, 1, false, null];
        yield 'strategy 1, app 3, attempt 4: not executed, requeue' => [4, 1, 3, false, false, 1, false, null];
        yield 'strategy 0, app 3, attempt 1: last attempt, requeue' => [1, 0, 3, false, true, 1, true, false];
        yield 'strategy 0, app 3, attempt 2: not executed, requeue' => [2, 0, 3, false, false, 1, false, null];

        // No retry strategy for the transport
        yield 'no strategy, app 3, attempt 1: last attempt, requeue' => [1, null, 3, false, true, 1, true, false];
        yield 'no strategy, app 3, attempt 2: not executed, requeue' => [2, null, 3, false, false, 1, false, null];

        // Unrecoverable throwable: not retried and not requeued
        yield 'unrecoverable, strategy 3, app 3, attempt 1' => [1, 3, 3, true, true, null, true, false];
        yield 'unrecoverable, strategy 3, app 3, attempt 2' => [2, 3, 3, true, true, null, true, false];
        yield 'unrecoverable, strategy 3, app 3, attempt 3' => [3, 3, 3, true, true, null, true, false];
        yield 'unrecoverable, strategy 0, app 3, attempt 1' => [1, 0, 3, true, true, null, true, false];
    }

    /**
     * @see testHandleFailedMessageLogsWhyItWillNotBeRetried
     */
    public static function provideRetryStopReasonData(): iterable
    {
        yield 'strategy 3, app 3, attempt 2: retry' => [2, 3, 3, false, null];
        yield 'strategy 3, app 3, attempt 3' => [3, 3, 3, false, 'app_max_retries'];
        yield 'strategy 3, app 5, attempt 4' => [4, 3, 5, false, 'retry_strategy'];
        yield 'strategy 3, app 4, attempt 4: both stop' => [4, 3, 4, false, 'retry_strategy'];
        yield 'strategy 1, app 3, attempt 2' => [2, 1, 3, false, 'retry_strategy'];
        yield 'no strategy, app 3, attempt 1' => [1, null, 3, false, 'retry_strategy'];
        yield 'unrecoverable, strategy 3, app 3, attempt 1' => [1, 3, 3, true, 'unrecoverable'];
    }

    #[DataProvider('provideFailedMessageData')]
    public function testHandleFailedMessage(
        int $receiveCount,
        ?int $maxRetries,
        int $appMaxRetries,
        bool $isUnrecoverable,
        bool $expectedExecuted,
        ?int $expectedVisibilityTimeout,
        bool $expectedWillNotBeRetriedLog,
        ?bool $expectedWillRetry,
    ): void {
        $throwable = $isUnrecoverable
            ? new UnrecoverableMessageHandlingException('Unrecoverable')
            : new RuntimeException('Failure');
        $bus = new MessageBusStub($throwable);
        $errorHandler = \interface_exists(ErrorHandlerInterface::class) ? new ErrorHandlerStub() : null;
        $failedEvents = [];
        $logHandler = new TestHandler();
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: $bus,
            maxRetries: $maxRetries,
            appMaxRetries: $appMaxRetries,
            logHandler: $logHandler,
            requestBodies: $requestBodies,
            failedEvents: $failedEvents,
            errorHandler: $errorHandler
        );

        $result = $sut->handle($this->createSqsEvent($receiveCount), $this->createContext());

        self::assertCount($expectedExecuted ? 1 : 0, $bus->getDispatchedEnvelopes());
        $expectedResult = ['batchItemFailures' => [['itemIdentifier' => self::MESSAGE_ID]]];
        self::assertSame($expectedVisibilityTimeout === null ? null : $expectedResult, $result);
        self::assertSame(
            $expectedVisibilityTimeout === null ? [] : [$expectedVisibilityTimeout],
            $this->getVisibilityTimeouts($requestBodies)
        );
        self::assertSame(
            $expectedWillNotBeRetriedLog,
            $logHandler->hasErrorThatContains('SQS Record failed to process but will not be retried')
        );
        self::assertSame(
            $isUnrecoverable,
            $logHandler->hasErrorThatContains('explicitly marked as unrecoverable')
        );
        self::assertSame(
            $expectedWillRetry === null ? [] : [$expectedWillRetry],
            \array_map(
                static fn (ServerlessWorkerMessageFailedEvent $event): bool => $event->willRetry(),
                $failedEvents
            )
        );

        if ($errorHandler !== null) {
            self::assertSame(
                $expectedWillRetry === null ? [] : [$expectedWillRetry],
                \array_map(
                    static fn (Throwable $reported): bool => $reported instanceof RetryableException
                        && $reported->willRetry(),
                    $errorHandler->getReportedThrowables()
                )
            );
        }
    }

    public function testHandleFailedMessageIgnoresSerializedRedeliveryStamp(): void
    {
        $bus = new MessageBusStub(new RuntimeException('Failure'));
        $failedEvents = [];
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: $bus,
            maxRetries: 3,
            appMaxRetries: 4,
            logHandler: new TestHandler(),
            requestBodies: $requestBodies,
            failedEvents: $failedEvents
        );

        $sut->handle($this->createSqsEvent(2, [new RedeliveryStamp(3)]), $this->createContext());

        self::assertCount(1, $bus->getDispatchedEnvelopes());
        self::assertSame(1, RedeliveryStamp::getRetryCountFromEnvelope($bus->getDispatchedEnvelopes()[0]));
        self::assertCount(1, $bus->getDispatchedEnvelopes()[0]->all(RedeliveryStamp::class));
        self::assertSame([80], $this->getVisibilityTimeouts($requestBodies));
    }

    #[DataProvider('provideRetryStopReasonData')]
    public function testHandleFailedMessageLogsWhyItWillNotBeRetried(
        int $receiveCount,
        ?int $maxRetries,
        int $appMaxRetries,
        bool $isUnrecoverable,
        ?string $expectedRetryStopReason,
    ): void {
        $throwable = $isUnrecoverable
            ? new UnrecoverableMessageHandlingException('Unrecoverable')
            : new RuntimeException('Failure');
        $failedEvents = [];
        $logHandler = new TestHandler();
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: new MessageBusStub($throwable),
            maxRetries: $maxRetries,
            appMaxRetries: $appMaxRetries,
            logHandler: $logHandler,
            requestBodies: $requestBodies,
            failedEvents: $failedEvents
        );

        $sut->handle($this->createSqsEvent($receiveCount), $this->createContext());

        $errors = \array_values(\array_filter(
            $logHandler->getRecords(),
            static fn (LogRecord $record): bool => $record->level === Level::Error
        ));
        self::assertSame(
            $expectedRetryStopReason === null ? [] : [[
                'app_max_retries' => $appMaxRetries,
                'attempt' => $receiveCount,
                'message_id' => self::MESSAGE_ID,
                'retry_stop_reason' => $expectedRetryStopReason,
            ]],
            \array_map(static fn (LogRecord $record): array => $record->context, $errors)
        );
    }

    public function testHandleFailedMessageThrowsOnLastAttemptWithoutPartialBatchFailure(): void
    {
        $failedEvents = [];
        $logHandler = new TestHandler();
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: new MessageBusStub(new RuntimeException('Failure')),
            maxRetries: 3,
            appMaxRetries: 3,
            logHandler: $logHandler,
            requestBodies: $requestBodies,
            failedEvents: $failedEvents,
            partialBatchFailure: false
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failure');

        try {
            $sut->handle($this->createSqsEvent(3), $this->createContext());
        } finally {
            self::assertTrue($logHandler->hasErrorThatContains('will not be retried'));
            self::assertSame([], $requestBodies);
        }
    }

    public function testHandleSucceedsWithRedeliveryStampHoldingRetryCount(): void
    {
        $bus = new MessageBusStub();
        $failedEvents = [];
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: $bus,
            maxRetries: 3,
            appMaxRetries: 3,
            logHandler: new TestHandler(),
            requestBodies: $requestBodies,
            failedEvents: $failedEvents
        );

        $result = $sut->handle($this->createSqsEvent(3), $this->createContext());

        self::assertNull($result);
        self::assertSame([], $requestBodies);
        self::assertSame([], $failedEvents);
        self::assertCount(1, $bus->getDispatchedEnvelopes());
        self::assertSame(2, RedeliveryStamp::getRetryCountFromEnvelope($bus->getDispatchedEnvelopes()[0]));
    }

    public function testHandleSucceedsWithoutRedeliveryStampOnFirstAttempt(): void
    {
        $bus = new MessageBusStub();
        $failedEvents = [];
        $requestBodies = [];
        $sut = $this->createSqsHandler(
            bus: $bus,
            maxRetries: 3,
            appMaxRetries: 3,
            logHandler: new TestHandler(),
            requestBodies: $requestBodies,
            failedEvents: $failedEvents
        );

        $sut->handle($this->createSqsEvent(1), $this->createContext());

        self::assertCount(1, $bus->getDispatchedEnvelopes());
        self::assertNull($bus->getDispatchedEnvelopes()[0]->last(RedeliveryStamp::class));
    }

    private function createContext(): Context
    {
        return new Context('request-id', (int)(\microtime(true) * 1000) + 300000, 'function-arn', '');
    }

    /**
     * @param \Symfony\Component\Messenger\Stamp\StampInterface[] $stamps
     */
    private function createSqsEvent(int $receiveCount, array $stamps = []): array
    {
        $encodedEnvelope = (new PhpSerializer())->encode(new Envelope(new stdClass(), $stamps));

        return [
            'Records' => [
                [
                    'attributes' => [
                        'ApproximateReceiveCount' => (string)$receiveCount,
                    ],
                    'body' => $encodedEnvelope['body'],
                    'eventSource' => 'aws:sqs',
                    'eventSourceARN' => 'arn:aws:sqs:ap-southeast-2:123456789012:queue',
                    'messageAttributes' => [],
                    'messageId' => self::MESSAGE_ID,
                    'receiptHandle' => 'receipt-handle',
                ],
            ],
        ];
    }

    /**
     * @param string[] $requestBodies
     * @param \EonX\EasyServerless\Event\ServerlessWorkerMessageFailedEvent[] $failedEvents
     */
    private function createSqsHandler(
        MessageBusStub $bus,
        ?int $maxRetries,
        int $appMaxRetries,
        TestHandler $logHandler,
        array &$requestBodies,
        array &$failedEvents,
        ?ErrorHandlerInterface $errorHandler = null,
        bool $partialBatchFailure = true,
    ): SqsHandler {
        $retryStrategies = [];

        if ($maxRetries !== null) {
            $retryStrategies[self::TRANSPORT_NAME] = static fn (): RetryStrategyInterface
                => new MultiplierRetryStrategy(
                    maxRetries: $maxRetries,
                    delayMilliseconds: 10000,
                    multiplier: 8,
                    maxDelayMilliseconds: 0,
                    jitter: 0
                );
        }

        $httpClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$requestBodies): MockResponse {
                $requestBodies[] = $options['body'];

                return new MockResponse('{"Successful":[],"Failed":[]}');
            }
        );

        $symfonyEventDispatcher = new EventDispatcher();
        $symfonyEventDispatcher->addListener(
            ServerlessWorkerMessageFailedEvent::class,
            static function (ServerlessWorkerMessageFailedEvent $event) use (&$failedEvents): void {
                $failedEvents[] = $event;
            }
        );

        return new SqsHandler(
            bus: $bus,
            serializer: new PhpSerializer(),
            retryStrategyLocator: new ServiceLocator($retryStrategies),
            sqsClient: new SqsClient(
                ['region' => 'ap-southeast-2'],
                new Credentials('access-key-id', 'secret-access-key'),
                $httpClient
            ),
            logger: new Logger('test', [$logHandler]),
            errorHandler: $errorHandler,
            transportName: self::TRANSPORT_NAME,
            appMaxRetries: $appMaxRetries,
            partialBatchFailure: $partialBatchFailure,
            symfonyEventDispatcher: $symfonyEventDispatcher
        );
    }

    /**
     * @param string[] $requestBodies
     *
     * @return int[]
     */
    private function getVisibilityTimeouts(array $requestBodies): array
    {
        $visibilityTimeouts = [];

        foreach ($requestBodies as $requestBody) {
            /** @var array{Entries: array<array{VisibilityTimeout: int}>} $request */
            $request = \json_decode($requestBody, true, flags: \JSON_THROW_ON_ERROR);

            foreach ($request['Entries'] as $entry) {
                $visibilityTimeouts[] = $entry['VisibilityTimeout'];
            }
        }

        return $visibilityTimeouts;
    }
}
