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
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLaravelTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\QueueManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final class SqsHandlerTest extends AbstractLaravelTestCase
{
    private const int APP_MAX_RETRIES = 3;

    private const string QUEUE_PREFIX = 'https://sqs.ap-southeast-2.amazonaws.com/123456789012';

    private int $jobFailedCount = 0;

    /**
     * @var array<int, \Aws\CommandInterface>
     */
    private array $sqsCommands = [];

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
        yield 'Successful job is acknowledged' => [
            'receiveCount' => 1,
            'exception' => null,
            'maxTries' => null,
            'backoff' => null,
            'expectedVisibilityTimeout' => null,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Failed job is retried on first attempt' => [
            'receiveCount' => 1,
            'exception' => 'boom',
            'maxTries' => null,
            'backoff' => null,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Failed job is retried with its backoff' => [
            'receiveCount' => 2,
            'exception' => 'boom',
            'maxTries' => null,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 30,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Failed job reaching app max retries is requeued without backoff' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'exception' => 'boom',
            'maxTries' => null,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 1,
        ];

        yield 'Failed job reaching its own max tries below app max retries is requeued without backoff' => [
            'receiveCount' => 2,
            'exception' => 'boom',
            'maxTries' => 2,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 1,
        ];

        yield 'Job exceeding its own max tries is requeued without running nor failing again' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'exception' => 'boom',
            'maxTries' => 2,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Job with max tries above app max retries is requeued at app max retries' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'exception' => 'boom',
            'maxTries' => 5,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Job with unlimited max tries is retried with its backoff' => [
            'receiveCount' => 2,
            'exception' => 'boom',
            'maxTries' => 0,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 30,
            'expectedJobFailedCount' => 0,
        ];

        // Laravel fails a job with a negative maxTries before running it, but it must still be retried
        // until the application max retries caps it instead of being requeued forever without running
        yield 'Job with negative max tries is retried with its backoff' => [
            'receiveCount' => 2,
            'exception' => 'boom',
            'maxTries' => -1,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 30,
            'expectedJobFailedCount' => 1,
        ];

        yield 'Job with unlimited max tries is requeued at app max retries without failing' => [
            'receiveCount' => self::APP_MAX_RETRIES,
            'exception' => 'boom',
            'maxTries' => 0,
            'backoff' => 30,
            'expectedVisibilityTimeout' => 1,
            'expectedJobFailedCount' => 0,
        ];

        yield 'Explicitly unrecoverable job is not retried' => [
            'receiveCount' => 1,
            'exception' => 'boom',
            'maxTries' => 1,
            'backoff' => null,
            'expectedVisibilityTimeout' => null,
            'expectedJobFailedCount' => 1,
        ];
    }

    public function testHandleSqsBatchOnlyReportsFailedRecords(): void
    {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(messageId: 'first', receiveCount: 1, exception: 'boom'),
                self::createSqsRecord(messageId: 'second', receiveCount: 1),
                self::createSqsRecord(messageId: 'third', receiveCount: 1, exception: 'boom'),
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
                    exception: 'boom',
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
        ?string $exception,
        ?int $maxTries,
        ?int $backoff,
        ?int $expectedVisibilityTimeout,
        int $expectedJobFailedCount,
    ): void {
        $handler = $this->createHandler();

        $result = $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(
                    messageId: 'message-id',
                    receiveCount: $receiveCount,
                    exception: $exception,
                    maxTries: $maxTries,
                    backoff: $backoff
                ),
            ]),
            self::createContext()
        );

        self::assertSame($expectedJobFailedCount, $this->jobFailedCount);

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

    public function testHandleSqsRecordThrowsJobExceptionWhenPartialBatchFailureDisabled(): void
    {
        $handler = $this->createHandler(partialBatchFailure: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $handler->handle(
            self::createSqsEvent([
                self::createSqsRecord(messageId: 'message-id', receiveCount: 1, exception: 'boom'),
            ]),
            self::createContext()
        );
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

    private static function createSqsEvent(array $records): array
    {
        return ['Records' => $records];
    }

    private static function createSqsRecord(
        string $messageId,
        int $receiveCount,
        ?string $exception = null,
        ?int $maxTries = null,
        ?int $backoff = null,
        ?string $messageGroupId = null,
    ): array {
        $body = \json_encode([
            'attempts' => 0,
            'backoff' => $backoff,
            'data' => ['exception' => $exception],
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

    private function createHandler(bool $partialBatchFailure = true): SqsHandler
    {
        $app = $this->getApplication([
            'cache' => [
                'default' => 'array',
                'stores' => [
                    'array' => [
                        'driver' => 'array',
                    ],
                ],
            ],
            'queue' => [
                'connections' => [
                    'sqs' => [
                        'driver' => 'sqs',
                        'prefix' => self::QUEUE_PREFIX,
                        'queue' => 'test',
                    ],
                ],
                'default' => 'sqs',
                'failed' => [
                    'driver' => 'null',
                ],
            ],
        ]);

        $app->alias('queue', QueueManager::class);
        $app->bind(ExceptionHandler::class, Handler::class);
        $app->instance('log', new NullLogger());
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
            ->extend('sqs', static fn(): SqsConnectorStub => new SqsConnectorStub($sqsClient));

        return new SqsHandler(
            container: $app,
            connectionName: 'sqs',
            appMaxRetries: self::APP_MAX_RETRIES,
            partialBatchFailure: $partialBatchFailure,
        );
    }
}
