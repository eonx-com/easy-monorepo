<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\SqsHandlers;

use Aws\CommandInterface;
use Aws\Result;
use Bref\Context\Context;
use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use EonX\EasyServerless\Tests\Stub\Laravel\ExceptionHandler\ExceptionHandlerStub;
use EonX\EasyServerless\Tests\Stub\Laravel\JobHandler\JobHandlerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as CacheRepositoryInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Illuminate\Contracts\Queue\Factory as QueueFactoryInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\QueueManager;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use stdClass;

final class SqsHandlerTest extends AbstractUnitTestCase
{
    private const MESSAGE_ID = 'message-id';

    protected function tearDown(): void
    {
        Container::setInstance();

        parent::tearDown();
    }

    /**
     * @see testHandleFailedJob
     */
    public static function provideFailedJobData(): iterable
    {
        // Job without tries, appMaxRetries is the maximum number of attempts
        yield 'tries null, app 3, attempt 1: retry in 1s' => [1, 3, null, null, null, true, 0, 1, false, false];
        yield 'tries null, app 3, attempt 2: retry in 1s' => [2, 3, null, null, null, true, 0, 1, false, false];
        yield 'tries null, app 3, attempt 3: last attempt' => [3, 3, null, null, null, true, 1, 1, true, false];
        yield 'tries null, app 3, attempt 4: not executed' => [4, 3, null, null, null, false, 0, 1, false, false];
        yield 'tries null, app 1, attempt 1: last attempt' => [1, 1, null, null, null, true, 1, 1, true, false];

        // Job backoff
        yield 'backoff, app 5, attempt 1: retry in 10s' => [1, 5, null, '10,80,640', null, true, 0, 10, false, false];
        yield 'backoff, app 5, attempt 2: retry in 80s' => [2, 5, null, '10,80,640', null, true, 0, 80, false, false];
        yield 'backoff, app 5, attempt 3: retry in 640s' => [3, 5, null, '10,80,640', null, true, 0, 640, false, false];
        yield 'backoff, app 5, attempt 4: retry in 640s' => [4, 5, null, '10,80,640', null, true, 0, 640, false, false];
        yield 'backoff, app 5, attempt 5: last attempt' => [5, 5, null, '10,80,640', null, true, 1, 1, true, false];
        yield 'backoff single, app 3, attempt 2: retry in 5s' => [2, 3, null, '5', null, true, 0, 5, false, false];

        // Job tries below appMaxRetries
        yield 'tries 2, app 3, attempt 1: retry in 1s' => [1, 3, 2, null, null, true, 0, 1, false, false];
        yield 'tries 2, app 3, attempt 2: last attempt' => [2, 3, 2, null, null, true, 1, 1, true, false];
        yield 'tries 2, app 3, attempt 3: not executed' => [3, 3, 2, null, null, false, 0, 1, false, false];

        // Job tries above appMaxRetries: appMaxRetries wins
        yield 'tries 5, app 3, attempt 2: retry in 1s' => [2, 3, 5, null, null, true, 0, 1, false, false];
        yield 'tries 5, app 3, attempt 3: last attempt' => [3, 3, 5, null, null, true, 1, 1, true, false];
        yield 'tries 0 (unlimited), app 3, attempt 3: last attempt' => [3, 3, 0, null, null, true, 1, 1, true, false];

        // Explicitly unrecoverable: not retried and not requeued
        yield 'tries 1, app 3, attempt 1: unrecoverable' => [1, 3, 1, null, null, true, 1, null, true, true];
        yield 'manual fail, app 3, attempt 1: failed early' => [1, 3, null, null, 'fail', true, 1, null, true, false];

        // Manual release
        yield 'release 30s, app 3, attempt 1: retry in 30s' => [1, 3, null, null, 'release', true, 0, 30, false, false];
        yield 'release 30s, app 3, attempt 3: last attempt' => [3, 3, null, null, 'release', true, 1, 1, true, false];
    }

    /**
     * @see testHandleFailedJobLogsWhyItWillNotBeRetried
     */
    public static function provideRetryStopReasonData(): iterable
    {
        yield 'tries null, app 3, attempt 2: retry' => [2, null, null, null];
        yield 'tries null, app 3, attempt 3' => [3, null, null, 'app_max_retries'];
        yield 'tries 5, app 3, attempt 3' => [3, 5, null, 'app_max_retries'];
        yield 'tries 0 (unlimited), app 3, attempt 3' => [3, 0, null, 'app_max_retries'];
        yield 'tries 2, app 3, attempt 2' => [2, 2, null, 'job_max_tries'];
        yield 'tries 3, app 3, attempt 3: both stop' => [3, 3, null, 'job_max_tries'];
        yield 'release 30s, app 3, attempt 3' => [3, null, 'release', 'app_max_retries'];
        yield 'tries 1, app 3, attempt 1' => [1, 1, null, 'unrecoverable'];
        yield 'manual fail, app 3, attempt 1' => [1, null, 'fail', 'job_failed'];
    }

    /**
     * @see testHandleFailedJobWarnsWhenTriesAreLimitedByAppMaxRetries
     */
    public static function provideTriesLimitedByAppMaxRetriesData(): iterable
    {
        yield 'tries 5, app 3, attempt 2: retry' => [2, 5, null];
        yield 'tries 5, app 3, attempt 3: last attempt' => [3, 5, 'allows 5 tries'];
        yield 'tries 0 (unlimited), app 3, attempt 3: last attempt' => [3, 0, 'allows unlimited tries'];
        yield 'tries 3, app 3, attempt 3: last attempt' => [3, 3, null];
        yield 'tries 2, app 3, attempt 2: last attempt' => [2, 2, null];
        yield 'tries null, app 3, attempt 3: last attempt' => [3, null, null];
        yield 'tries 1, app 3, attempt 1: unrecoverable' => [1, 1, null];
    }

    #[DataProvider('provideFailedJobData')]
    public function testHandleFailedJob(
        int $receiveCount,
        int $appMaxRetries,
        ?int $tries,
        ?string $backoff,
        ?string $behaviour,
        bool $expectedExecuted,
        int $expectedFailedCount,
        ?int $expectedVisibilityTimeout,
        bool $expectedWillNotBeRetriedLog,
        bool $expectedUnrecoverableLog,
    ): void {
        $jobHandler = new JobHandlerStub(
            throwable: new RuntimeException('Failure'),
            failManually: $behaviour === 'fail',
            releaseDelay: $behaviour === 'release' ? 30 : null
        );
        $logHandler = new TestHandler();
        $commands = [];
        $sut = $this->createSqsHandler($jobHandler, $appMaxRetries, $logHandler, $commands);

        $result = $sut->handle($this->createSqsEvent($receiveCount, $tries, $backoff), $this->createContext());

        $expectedResult = ['batchItemFailures' => [['itemIdentifier' => self::MESSAGE_ID]]];
        self::assertSame($expectedExecuted ? 1 : 0, $jobHandler->getFiredCount());
        self::assertSame($expectedFailedCount, $jobHandler->getFailedCount());
        self::assertSame($expectedVisibilityTimeout === null ? null : $expectedResult, $result);
        self::assertSame(
            $expectedVisibilityTimeout === null ? [] : [$expectedVisibilityTimeout],
            $this->getVisibilityTimeouts($commands)
        );
        self::assertSame(
            $expectedWillNotBeRetriedLog,
            $logHandler->hasErrorThatContains('SQS Record failed to process but will not be retried')
        );
        self::assertSame(
            $expectedUnrecoverableLog,
            $logHandler->hasErrorThatContains('explicitly marked as unrecoverable')
        );
    }

    #[DataProvider('provideRetryStopReasonData')]
    public function testHandleFailedJobLogsWhyItWillNotBeRetried(
        int $receiveCount,
        ?int $tries,
        ?string $behaviour,
        ?string $expectedRetryStopReason,
    ): void {
        $jobHandler = new JobHandlerStub(
            throwable: new RuntimeException('Failure'),
            failManually: $behaviour === 'fail',
            releaseDelay: $behaviour === 'release' ? 30 : null
        );
        $logHandler = new TestHandler();
        $commands = [];
        $sut = $this->createSqsHandler($jobHandler, 3, $logHandler, $commands);

        $sut->handle($this->createSqsEvent($receiveCount, $tries, null), $this->createContext());

        $errors = \array_values(\array_filter(
            $logHandler->getRecords(),
            static fn (LogRecord $record): bool => $record->level === Level::Error
        ));
        self::assertSame(
            $expectedRetryStopReason === null ? [] : [[
                'app_max_retries' => 3,
                'attempt' => $receiveCount,
                'message_id' => self::MESSAGE_ID,
                'retry_stop_reason' => $expectedRetryStopReason,
            ]],
            \array_map(static fn (LogRecord $record): array => $record->context, $errors)
        );
    }

    public function testHandleFailedJobThrowsWithoutPartialBatchFailure(): void
    {
        $commands = [];
        $sut = $this->createSqsHandler(
            jobHandler: new JobHandlerStub(new RuntimeException('Failure')),
            appMaxRetries: 3,
            logHandler: new TestHandler(),
            commands: $commands,
            partialBatchFailure: false
        );
        $this->expectException(RuntimeException::class);

        try {
            $sut->handle($this->createSqsEvent(1, null, null), $this->createContext());
        } finally {
            self::assertSame([], $commands);
        }
    }

    #[DataProvider('provideTriesLimitedByAppMaxRetriesData')]
    public function testHandleFailedJobWarnsWhenTriesAreLimitedByAppMaxRetries(
        int $receiveCount,
        ?int $tries,
        ?string $expectedWarning,
    ): void {
        $logHandler = new TestHandler();
        $commands = [];
        $sut = $this->createSqsHandler(new JobHandlerStub(new RuntimeException('Failure')), 3, $logHandler, $commands);

        $sut->handle($this->createSqsEvent($receiveCount, $tries, null), $this->createContext());

        $warnings = \array_filter(
            $logHandler->getRecords(),
            static fn (LogRecord $record): bool => $record->level === Level::Warning
        );
        self::assertCount($expectedWarning === null ? 0 : 1, $warnings);

        if ($expectedWarning !== null) {
            self::assertTrue($logHandler->hasWarningThatContains($expectedWarning));
            self::assertTrue($logHandler->hasWarningThatContains('the SQS handler allows only 3 attempts'));
        }
    }

    public function testHandleReleasedJobOnLastAttemptWhenFailedMethodThrows(): void
    {
        $jobHandler = new JobHandlerStub(
            releaseDelay: 30,
            failedThrowable: new RuntimeException('Failed method error')
        );
        $logHandler = new TestHandler();
        $commands = [];
        $sut = $this->createSqsHandler($jobHandler, 3, $logHandler, $commands);

        $result = $sut->handle($this->createSqsEvent(3, null, null), $this->createContext());

        self::assertSame(1, $jobHandler->getFailedCount());
        self::assertSame(['batchItemFailures' => [['itemIdentifier' => self::MESSAGE_ID]]], $result);
        self::assertSame([1], $this->getVisibilityTimeouts($commands));
        self::assertTrue($logHandler->hasErrorThatContains('Error while failing the job'));
        self::assertTrue($logHandler->hasErrorThatContains('will not be retried'));
    }

    public function testHandleSucceeds(): void
    {
        $jobHandler = new JobHandlerStub();
        $commands = [];
        $sut = $this->createSqsHandler($jobHandler, 3, new TestHandler(), $commands);

        $result = $sut->handle($this->createSqsEvent(2, null, null), $this->createContext());

        self::assertNull($result);
        self::assertSame([], $commands);
        self::assertSame(1, $jobHandler->getFiredCount());
        self::assertSame(0, $jobHandler->getFailedCount());
    }

    private function createContext(): Context
    {
        return new Context('request-id', (int)(\microtime(true) * 1000) + 300000, 'function-arn', '');
    }

    private function createSqsEvent(int $receiveCount, ?int $tries, ?string $backoff): array
    {
        $payload = [
            'backoff' => $backoff,
            'data' => [],
            'displayName' => JobHandlerStub::class,
            'job' => JobHandlerStub::class . '@fire',
            'maxTries' => $tries,
            'uuid' => 'uuid',
        ];

        return [
            'Records' => [
                [
                    'attributes' => [
                        'ApproximateReceiveCount' => (string)$receiveCount,
                    ],
                    'body' => \json_encode($payload, \JSON_THROW_ON_ERROR),
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
     * @param \Aws\CommandInterface[] $commands
     */
    private function createSqsHandler(
        JobHandlerStub $jobHandler,
        int $appMaxRetries,
        TestHandler $logHandler,
        array &$commands,
        bool $partialBatchFailure = true,
    ): SqsHandler {
        $container = new Container();
        Container::setInstance($container);

        $awsHandler = static function (CommandInterface $command, RequestInterface $request) use (
            &$commands
        ): PromiseInterface {
            $commands[] = $command;

            return Create::promiseFor(new Result([]));
        };

        $container->instance('config', new ConfigRepository([
            'queue' => [
                'connections' => [
                    'sqs' => [
                        'driver' => 'sqs',
                        'handler' => $awsHandler,
                        'key' => 'key',
                        'prefix' => 'https://sqs.ap-southeast-2.amazonaws.com/123456789012',
                        'queue' => 'queue',
                        'region' => 'ap-southeast-2',
                        'secret' => 'secret',
                    ],
                ],
                'default' => 'sqs',
            ],
        ]));

        $events = new Dispatcher($container);
        $container->instance('events', $events);
        $container->instance(DispatcherInterface::class, $events);
        $container->instance(ExceptionHandler::class, new ExceptionHandlerStub());
        $container->instance(CacheRepositoryInterface::class, new CacheRepository(new ArrayStore()));
        $container->instance('db', new stdClass());
        $container->instance('log', new Logger('test', [$logHandler]));
        $container->instance(JobHandlerStub::class, $jobHandler);

        // The queue manager only needs the container to resolve the config
        // @phpstan-ignore argument.type
        $queueManager = new QueueManager($container);
        $queueManager->addConnector('sqs', static fn (): SqsConnector => new SqsConnector());
        $container->instance(QueueManager::class, $queueManager);
        $container->instance(QueueFactoryInterface::class, $queueManager);

        return new SqsHandler(
            container: $container,
            connectionName: 'sqs',
            appMaxRetries: $appMaxRetries,
            partialBatchFailure: $partialBatchFailure
        );
    }

    /**
     * @param \Aws\CommandInterface[] $commands
     *
     * @return int[]
     */
    private function getVisibilityTimeouts(array $commands): array
    {
        $visibilityTimeouts = [];

        foreach ($commands as $command) {
            if ($command->getName() !== 'ChangeMessageVisibilityBatch') {
                continue;
            }

            /** @var array<array{VisibilityTimeout: int}> $entries */
            $entries = $command['Entries'];

            foreach ($entries as $entry) {
                $visibilityTimeouts[] = $entry['VisibilityTimeout'];
            }
        }

        return $visibilityTimeouts;
    }
}
