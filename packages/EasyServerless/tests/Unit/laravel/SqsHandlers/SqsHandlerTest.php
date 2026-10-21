<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\SqsHandlers;

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\Sqs\SqsClient;
use Bref\Context\Context;
use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use EonX\EasyServerless\Tests\Stub\Connector\SqsConnectorStub;
use EonX\EasyServerless\Tests\Stub\JobHandler\JobHandlerStub;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\QueueManager;
use Laravel\Lumen\Exceptions\Handler;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final class SqsHandlerTest extends AbstractLumenTestCase
{
    private const APP_MAX_RETRIES = 3;

    private const QUEUE_PREFIX = 'https://sqs.ap-southeast-2.amazonaws.com/123456789012';

    private int $jobFailedCount = 0;

    private TestHandler $logHandler;

    /**
     * @var array<int, \Aws\CommandInterface>
     */
    private array $sqsCommands = [];

    protected function setUp(): void
    {
        parent::setUp();

        JobHandlerStub::reset();
        $this->logHandler = new TestHandler();
    }

    protected function tearDown(): void
    {
        // Lumen application registers its own error and exception handlers on construction
        \restore_error_handler();
        \restore_exception_handler();

        parent::tearDown();
    }

    /**
     * @see testHandleSqsFifoBatchSkipsMessageGroupAfterRequeue
     */
    public static function provideHandleSqsFifoBatchCases(): iterable
    {
        yield 'Failed job reaching app max retries' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'maxTries' => null,
        ];

        yield 'Failed job reaching its own max tries' => [
            'receiveCount' => 2,
            'maxTries' => 2,
        ];

        yield 'Job exceeding its own max tries' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'maxTries' => 2,
        ];
    }

    /**
     * @see testHandleSqsRecord
     */
    public static function provideHandleSqsRecordCases(): iterable
    {
        yield 'Successful job is acknowledged' => self::createRecordCase(
            receiveCount: 1,
            data: [],
            expectedVisibilityTimeout: null,
            expectedJobFailedCount: 0,
        );

        // Retries with the delay given to release(), computed by the worker from the backoff of the job
        yield 'Failed job is retried on first attempt' => self::createRecordCase(
            receiveCount: 1,
            data: ['exception' => 'boom'],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 0,
        );

        yield 'Failed job is retried with its backoff' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            backoff: 30,
            expectedVisibilityTimeout: 30,
            expectedJobFailedCount: 0,
        );

        yield 'Failed job is retried with the backoff of its attempt (attempt 1)' => self::createRecordCase(
            receiveCount: 1,
            appMaxRetries: 5,
            data: ['exception' => 'boom'],
            backoff: '10,80,640',
            expectedVisibilityTimeout: 10,
            expectedJobFailedCount: 0,
        );

        yield 'Failed job is retried with the backoff of its attempt (attempt 3)' => self::createRecordCase(
            receiveCount: 3,
            appMaxRetries: 5,
            data: ['exception' => 'boom'],
            backoff: '10,80,640',
            expectedVisibilityTimeout: 640,
            expectedJobFailedCount: 0,
        );

        yield 'Failed job is retried with the last backoff after the list' => self::createRecordCase(
            receiveCount: 4,
            appMaxRetries: 5,
            data: ['exception' => 'boom'],
            backoff: '10,80,640',
            expectedVisibilityTimeout: 640,
            expectedJobFailedCount: 0,
        );

        // Explicit release delay
        yield 'Job released with a delay is retried after it' => self::createRecordCase(
            receiveCount: 1,
            data: ['release' => 60],
            expectedVisibilityTimeout: 60,
            expectedJobFailedCount: 0,
        );

        yield 'Job released without delay is retried after the minimum delay' => self::createRecordCase(
            receiveCount: 1,
            data: ['release' => 0],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 0,
        );

        yield 'Job released with a delay above the SQS limit is retried before the SQS limit' => self::createRecordCase(
            receiveCount: 1,
            data: ['release' => 100000],
            expectedVisibilityTimeout: 42000,
            expectedJobFailedCount: 0,
        );

        yield 'Explicit release delay wins over the backoff of the job' => self::createRecordCase(
            receiveCount: 1,
            data: ['release' => 60],
            backoff: 30,
            expectedVisibilityTimeout: 60,
            expectedJobFailedCount: 0,
        );

        // Last attempt: the job is failed and the message is requeued for the DLQ
        yield 'Failed job reaching app max retries is requeued without backoff' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            backoff: 30,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'app_max_retries',
        );

        yield 'Failed job reaching app max retries of 1 is requeued' => self::createRecordCase(
            receiveCount: 1,
            appMaxRetries: 1,
            data: ['exception' => 'boom'],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'app_max_retries',
        );

        yield 'Failed job reaching its own max tries below app max retries is requeued' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            maxTries: 2,
            backoff: 30,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'job_max_tries',
        );

        yield 'Failed job reaching its own max tries equal to app max retries is requeued' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            maxTries: self::APP_MAX_RETRIES,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'job_max_tries',
        );

        yield 'Job released on its last attempt is failed and requeued' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['release' => 60],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'app_max_retries',
        );

        // Job max tries above app max retries or unlimited: app max retries caps the attempts
        yield 'Job with max tries above app max retries is retried before app max retries' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            maxTries: 5,
            backoff: 30,
            expectedVisibilityTimeout: 30,
            expectedJobFailedCount: 0,
        );

        yield 'Job with max tries above app max retries is failed at app max retries' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            maxTries: 5,
            backoff: 30,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'app_max_retries',
        );

        yield 'Job with unlimited max tries is retried with its backoff' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            maxTries: 0,
            backoff: 30,
            expectedVisibilityTimeout: 30,
            expectedJobFailedCount: 0,
        );

        yield 'Job with negative max tries is run and retried with its backoff' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            maxTries: -1,
            backoff: 30,
            expectedVisibilityTimeout: 30,
            expectedJobFailedCount: 0,
        );

        yield 'Job with negative max tries is failed and requeued at app max retries' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            maxTries: -1,
            expectedRetryStopReason: 'app_max_retries',
        );

        yield 'Job with unlimited max tries is failed and requeued at app max retries' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            maxTries: 0,
            backoff: 30,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'app_max_retries',
        );

        // Requeued without running
        yield 'Job exceeding its own max tries is requeued without running nor failing again' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES,
            data: ['exception' => 'boom'],
            maxTries: 2,
            backoff: 30,
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 0,
            expectedFired: false,
        );

        yield 'Job exceeding app max retries is requeued without running' => self::createRecordCase(
            receiveCount: self::APP_MAX_RETRIES + 1,
            data: ['exception' => 'boom'],
            expectedVisibilityTimeout: 1,
            expectedJobFailedCount: 0,
            expectedFired: false,
        );

        // Not requeued: SQS deletes the message
        yield 'Explicitly unrecoverable job is not retried' => self::createRecordCase(
            receiveCount: 1,
            data: ['exception' => 'boom'],
            maxTries: 1,
            expectedVisibilityTimeout: null,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'unrecoverable',
        );

        yield 'Explicitly unrecoverable job received again is failed without running' => self::createRecordCase(
            receiveCount: 2,
            data: ['exception' => 'boom'],
            maxTries: 1,
            expectedVisibilityTimeout: null,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'unrecoverable',
            expectedFired: false,
        );

        yield 'Job failing itself before its last attempt is not requeued' => self::createRecordCase(
            receiveCount: 1,
            data: ['fail' => true],
            expectedVisibilityTimeout: null,
            expectedJobFailedCount: 1,
            expectedRetryStopReason: 'job_failed',
        );
    }

    /**
     * @see testHandleSqsRecordWarnsWhenJobMaxTriesIsLimitedByAppMaxRetries
     */
    public static function provideJobMaxTriesLimitedByAppMaxRetriesCases(): iterable
    {
        yield 'Above app max retries, before last attempt' => [2, 5, null];
        yield 'Above app max retries, last attempt' => [self::APP_MAX_RETRIES, 5, 'allows 5 tries'];
        yield 'Unlimited, last attempt' => [self::APP_MAX_RETRIES, 0, 'allows unlimited tries'];
        yield 'Negative, last attempt' => [self::APP_MAX_RETRIES, -1, 'allows unlimited tries'];
        yield 'Equal to app max retries, last attempt' => [self::APP_MAX_RETRIES, self::APP_MAX_RETRIES, null];
        yield 'Below app max retries, last attempt' => [2, 2, null];
        yield 'Not set, last attempt' => [self::APP_MAX_RETRIES, null, null];
        yield 'Explicitly unrecoverable' => [1, 1, null];
    }

    public function testHandleReleasedJobOnLastAttemptWhenFailedMethodThrows(): void
    {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'message-id',
                    receiveCount: self::APP_MAX_RETRIES,
                    data: ['failedException' => 'failed() error', 'release' => 60]
                ),
            ]),
            self::createContext()
        );

        self::assertSame(['batchItemFailures' => [['itemIdentifier' => 'message-id']]], $result);
        self::assertSame([1], $this->getVisibilityTimeouts());
        self::assertTrue($this->logHandler->hasErrorThatContains('Error while failing the job'));
        self::assertTrue($this->logHandler->hasErrorThatContains('will not be retried'));
    }

    public function testHandleSqsBatchOnlyReportsFailedRecords(): void
    {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(messageId: 'first', receiveCount: 1, data: ['exception' => 'boom']),
                self::createSqsRecord(messageId: 'second', receiveCount: 1),
                self::createSqsRecord(messageId: 'third', receiveCount: 1, data: ['exception' => 'boom']),
            ]),
            self::createContext()
        );

        self::assertSame(
            ['batchItemFailures' => [['itemIdentifier' => 'first'], ['itemIdentifier' => 'third']]],
            $result
        );
        self::assertCount(1, $this->sqsCommands);
        $entries = $this->sqsCommands[0]['Entries'];
        self::assertIsArray($entries);
        self::assertSame(['first', 'third'], \array_column($entries, 'Id'));
    }

    #[DataProvider('provideHandleSqsFifoBatchCases')]
    public function testHandleSqsFifoBatchSkipsMessageGroupAfterRequeue(int $receiveCount, ?int $maxTries): void
    {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'first',
                    receiveCount: $receiveCount,
                    data: ['exception' => 'boom'],
                    maxTries: $maxTries,
                    messageGroupId: 'group-1'
                ),
                self::createSqsRecord(messageId: 'second', receiveCount: 1, messageGroupId: 'group-1'),
                self::createSqsRecord(messageId: 'third', receiveCount: 1, messageGroupId: 'group-2'),
            ]),
            self::createContext()
        );

        // The requeued record and the following one from the same group are both reported to preserve order
        self::assertSame(
            ['batchItemFailures' => [['itemIdentifier' => 'first'], ['itemIdentifier' => 'second']]],
            $result
        );
        self::assertCount(1, $this->sqsCommands);
        $command = $this->sqsCommands[0];
        self::assertSame(self::QUEUE_PREFIX . '/test.fifo', $command['QueueUrl']);
        self::assertSame(
            [
                ['Id' => 'first', 'ReceiptHandle' => 'receipt-first', 'VisibilityTimeout' => 1],
                ['Id' => 'second', 'ReceiptHandle' => 'receipt-second', 'VisibilityTimeout' => 1],
            ],
            $command['Entries']
        );
    }

    #[DataProvider('provideHandleSqsRecordCases')]
    public function testHandleSqsRecord(
        int $receiveCount,
        array $data,
        ?int $expectedVisibilityTimeout,
        int $expectedJobFailedCount,
        ?int $appMaxRetries = null,
        ?int $maxTries = null,
        int|string|null $backoff = null,
        ?string $expectedRetryStopReason = null,
        bool $expectedFired = true,
    ): void {
        $appMaxRetries ??= self::APP_MAX_RETRIES;
        $handler = $this->createHandler(appMaxRetries: $appMaxRetries);

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'message-id',
                    receiveCount: $receiveCount,
                    data: $data,
                    maxTries: $maxTries,
                    backoff: $backoff
                ),
            ]),
            self::createContext()
        );

        self::assertSame($expectedFired ? 1 : 0, JobHandlerStub::getFiredCount());
        self::assertSame($expectedJobFailedCount, $this->jobFailedCount);
        self::assertSame(
            $expectedRetryStopReason === null ? [] : [[
                'app_max_retries' => $appMaxRetries,
                'attempt' => $receiveCount,
                'message_id' => 'message-id',
                'retry_stop_reason' => $expectedRetryStopReason,
            ]],
            \array_map(static fn (LogRecord $record): array => $record->context, $this->getLogRecords(Level::Error))
        );

        if ($expectedVisibilityTimeout === null) {
            self::assertNull($result);
            self::assertSame([], $this->sqsCommands);

            return;
        }

        self::assertSame(['batchItemFailures' => [['itemIdentifier' => 'message-id']]], $result);
        self::assertCount(1, $this->sqsCommands);
        $command = $this->sqsCommands[0];
        self::assertSame('ChangeMessageVisibilityBatch', $command->getName());
        self::assertSame(self::QUEUE_PREFIX . '/test', $command['QueueUrl']);
        self::assertSame(
            [
                [
                    'Id' => 'message-id',
                    'ReceiptHandle' => 'receipt-message-id',
                    'VisibilityTimeout' => $expectedVisibilityTimeout,
                ],
            ],
            $command['Entries']
        );
    }

    public function testHandleSqsRecordRequeuesJobExceedingItsMaxTriesWithPartialBatchFailure(): void
    {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'message-id',
                    receiveCount: 3,
                    data: ['exception' => 'boom'],
                    maxTries: 2
                ),
            ]),
            self::createContext()
        );

        self::assertSame(['batchItemFailures' => [['itemIdentifier' => 'message-id']]], $result);
        self::assertSame([1], $this->getVisibilityTimeouts());
        self::assertSame(0, JobHandlerStub::getFiredCount());
    }

    public function testHandleSqsRecordThrowsForJobExceedingItsMaxTriesWhenPartialBatchFailureDisabled(): void
    {
        $handler = $this->createHandler(partialBatchFailure: false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SQS Record "message-id" has reached the job max tries (2)');

        try {
            $handler->handle(
                self::createSqsEvent([
                    self::createSqsRecord(
                        messageId: 'message-id',
                        receiveCount: 3,
                        data: ['exception' => 'boom'],
                        maxTries: 2
                    ),
                ]),
                self::createContext()
            );
        } finally {
            self::assertSame(0, JobHandlerStub::getFiredCount());
            self::assertSame(0, $this->jobFailedCount);
            self::assertSame([], $this->sqsCommands);
        }
    }

    public function testHandleSqsRecordThrowsForReleasedJobWhenPartialBatchFailureDisabled(): void
    {
        $handler = $this->createHandler(partialBatchFailure: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('was released and will be retried');

        $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(messageId: 'message-id', receiveCount: 1, data: ['release' => 60]),
            ]),
            self::createContext()
        );
    }

    public function testHandleSqsRecordThrowsJobExceptionOnLastAttemptWhenPartialBatchFailureDisabled(): void
    {
        $handler = $this->createHandler(partialBatchFailure: false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('boom');

        try {
            $handler->handle(
                self::createSqsEvent([
                    self::createSqsRecord(
                        messageId: 'message-id',
                        receiveCount: self::APP_MAX_RETRIES,
                        data: ['exception' => 'boom']
                    ),
                ]),
                self::createContext()
            );
        } finally {
            self::assertSame(1, $this->jobFailedCount);
            self::assertTrue($this->logHandler->hasErrorThatContains('will not be retried'));
        }
    }

    public function testHandleSqsRecordThrowsJobExceptionWhenPartialBatchFailureDisabled(): void
    {
        $handler = $this->createHandler(partialBatchFailure: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(messageId: 'message-id', receiveCount: 1, data: ['exception' => 'boom']),
            ]),
            self::createContext()
        );
    }

    #[DataProvider('provideJobMaxTriesLimitedByAppMaxRetriesCases')]
    public function testHandleSqsRecordWarnsWhenJobMaxTriesIsLimitedByAppMaxRetries(
        int $receiveCount,
        ?int $maxTries,
        ?string $expectedWarning,
    ): void {
        $handler = $this->createHandler();

        $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'message-id',
                    receiveCount: $receiveCount,
                    data: ['exception' => 'boom'],
                    maxTries: $maxTries
                ),
            ]),
            self::createContext()
        );

        self::assertCount($expectedWarning === null ? 0 : 1, $this->getLogRecords(Level::Warning));

        if ($expectedWarning !== null) {
            self::assertTrue($this->logHandler->hasWarningThatContains($expectedWarning));
            self::assertTrue($this->logHandler->hasWarningThatContains('the SQS handler allows only 3 attempts'));
        }
    }

    private static function createContext(): Context
    {
        return new Context(
            'aws-request-id',
            (int)(\microtime(true) * 1000) + 60000,
            'arn:aws:lambda:ap-southeast-2:123456789012:function:test',
            'trace-id'
        );
    }

    /**
     * Returns the arguments in the order of testHandleSqsRecord(), as PHPUnit 10 ignores the data set keys.
     */
    private static function createRecordCase(
        int $receiveCount,
        array $data,
        ?int $expectedVisibilityTimeout,
        int $expectedJobFailedCount,
        ?int $appMaxRetries = null,
        ?int $maxTries = null,
        int|string|null $backoff = null,
        ?string $expectedRetryStopReason = null,
        bool $expectedFired = true,
    ): array {
        return [
            $receiveCount,
            $data,
            $expectedVisibilityTimeout,
            $expectedJobFailedCount,
            $appMaxRetries,
            $maxTries,
            $backoff,
            $expectedRetryStopReason,
            $expectedFired,
        ];
    }

    private static function createSqsEvent(array $records): array
    {
        return ['Records' => $records];
    }

    private static function createSqsRecord(
        string $messageId,
        int $receiveCount,
        array $data = [],
        ?int $maxTries = null,
        int|string|null $backoff = null,
        ?string $messageGroupId = null,
    ): array {
        $body = \json_encode([
            'attempts' => 0,
            'backoff' => $backoff,
            'data' => $data,
            'displayName' => JobHandlerStub::class,
            'job' => JobHandlerStub::class . '@fire',
            'maxExceptions' => null,
            'maxTries' => $maxTries,
            'retryUntil' => null,
            'timeout' => null,
            'uuid' => $messageId,
        ], \JSON_THROW_ON_ERROR);

        $attributes = [
            'ApproximateFirstReceiveTimestamp' => '0',
            'ApproximateReceiveCount' => (string)$receiveCount,
            'SenderId' => 'sender-id',
            'SentTimestamp' => '0',
        ];

        $queueName = 'test';

        if ($messageGroupId !== null) {
            $attributes['MessageDeduplicationId'] = 'dedup-' . $messageId;
            $attributes['MessageGroupId'] = $messageGroupId;
            $queueName = 'test.fifo';
        }

        return [
            'attributes' => $attributes,
            'awsRegion' => 'ap-southeast-2',
            'body' => $body,
            'eventSource' => 'aws:sqs',
            'eventSourceARN' => 'arn:aws:sqs:ap-southeast-2:123456789012:' . $queueName,
            'md5OfBody' => \md5($body),
            'messageAttributes' => [],
            'messageId' => $messageId,
            'receiptHandle' => 'receipt-' . $messageId,
        ];
    }

    private function createHandler(
        bool $partialBatchFailure = true,
        int $appMaxRetries = self::APP_MAX_RETRIES,
    ): SqsHandler {
        $app = $this->getApplication([]);
        $app->configure('cache');
        $app->configure('queue');

        \config([
            'cache.default' => 'array',
            'queue.connections.sqs' => [
                'driver' => 'sqs',
                'prefix' => self::QUEUE_PREFIX,
                'queue' => 'test',
            ],
            'queue.default' => 'sqs',
        ]);

        $app->alias('queue', QueueManager::class);
        $app->bind(ExceptionHandler::class, Handler::class);
        // The handler logs to "log", the Lumen exception handler reports to LoggerInterface
        $app->instance('log', new Logger('test', [$this->logHandler]));
        $app->instance(LoggerInterface::class, new NullLogger());
        $app->make(Dispatcher::class)
            ->listen(JobFailed::class, function (): void {
                $this->jobFailedCount++;
            });

        $recordCommand = function (CommandInterface $command): Result {
            $this->sqsCommands[] = $command;

            return new Result();
        };
        $sqsClient = new SqsClient([
            'credentials' => false,
            'handler' => new MockHandler(\array_fill(0, 10, $recordCommand)),
            'region' => 'ap-southeast-2',
            'version' => 'latest',
        ]);

        $app->make(QueueManager::class)
            ->extend('sqs', static fn (): SqsConnectorStub => new SqsConnectorStub($sqsClient));

        return new SqsHandler(
            container: $app,
            connectionName: 'sqs',
            appMaxRetries: $appMaxRetries,
            partialBatchFailure: $partialBatchFailure,
        );
    }

    /**
     * @return \Monolog\LogRecord[]
     */
    private function getLogRecords(Level $level): array
    {
        return \array_values(\array_filter(
            $this->logHandler->getRecords(),
            static fn (LogRecord $record): bool => $record->level === $level
        ));
    }

    /**
     * @return int[]
     */
    private function getVisibilityTimeouts(): array
    {
        $visibilityTimeouts = [];

        foreach ($this->sqsCommands as $command) {
            /** @var array<array{VisibilityTimeout: int}> $entries */
            $entries = $command['Entries'];

            foreach ($entries as $entry) {
                $visibilityTimeouts[] = $entry['VisibilityTimeout'];
            }
        }

        return $visibilityTimeouts;
    }
}
