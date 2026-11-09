<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Messenger\Middleware;

use EonX\EasyLock\Common\Exception\ShouldRetryException;
use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware;
use EonX\EasyLock\Messenger\Stamp\LockNotAcquiredStamp;
use EonX\EasyLock\Messenger\Stamp\WithLockDataStamp;
use EonX\EasyLock\Tests\Stub\Logger\LoggerStub;
use EonX\EasyLock\Tests\Stub\Message\WithLockDataMessageStub;
use EonX\EasyLock\Tests\Stub\Middleware\HandlerMiddlewareStub;
use EonX\EasyLock\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel;
use stdClass;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

final class ProcessWithLockMiddlewareTest extends AbstractUnitTestCase
{
    private const RESOURCE = 'some-resource';

    private HandlerMiddlewareStub $handler;

    private Locker $locker;

    private LoggerStub $logger;

    protected function setUp(): void
    {
        $this->handler = new HandlerMiddlewareStub();
        $this->locker = new Locker(new InMemoryStore());
        $this->logger = new LoggerStub();
    }

    /**
     * @see testItHandlesMessageWhenLockAcquired
     */
    public static function provideLockedEnvelopes(): iterable
    {
        yield 'message with lock data' => [
            new Envelope(
                new WithLockDataMessageStub(LockData::create(self::RESOURCE)),
                [new ConsumedByWorkerStamp()]
            ),
        ];

        yield 'envelope with lock data stamp' => [
            new Envelope(new stdClass(), [new ConsumedByWorkerStamp(), new WithLockDataStamp(self::RESOURCE)]),
        ];
    }

    /**
     * @see testItDoesNotParticipate
     */
    public static function provideNotParticipatingEnvelopes(): iterable
    {
        yield 'message without lock data' => [
            new Envelope(new stdClass(), [new ConsumedByWorkerStamp()]),
        ];

        yield 'message with lock data not consumed by worker' => [
            new Envelope(new WithLockDataMessageStub(LockData::create(self::RESOURCE))),
        ];

        yield 'envelope with lock data stamp not consumed by worker' => [
            new Envelope(new stdClass(), [new WithLockDataStamp(self::RESOURCE)]),
        ];
    }

    #[DataProvider('provideNotParticipatingEnvelopes')]
    public function testItDoesNotParticipate(Envelope $envelope): void
    {
        // Lock is held, so the handler is called only if the middleware does not participate
        $lock = $this->locker->createLock(self::RESOURCE);
        $lock->acquire();

        $result = $this->createMiddleware()
            ->handle($envelope, new StackMiddleware($this->handler));

        self::assertSame(1, $this->handler->getHandleCallCount());
        self::assertNotNull($result->last(HandledStamp::class));
        self::assertNull($result->last(LockNotAcquiredStamp::class));
        self::assertSame([], $this->logger->getRecords());

        $lock->release();
    }

    public function testItDropsMessageAndLogsWhenLockNotAcquired(): void
    {
        $lock = $this->locker->createLock(self::RESOURCE);
        $lock->acquire();
        $envelope = new Envelope(
            new WithLockDataMessageStub(LockData::create(self::RESOURCE, 600.0)),
            [new ConsumedByWorkerStamp(), new ReceivedStamp('async'), new TransportMessageIdStamp('message-id')]
        );

        $result = $this->createMiddleware(LogLevel::ERROR)
            ->handle($envelope, new StackMiddleware($this->handler));

        self::assertSame(0, $this->handler->getHandleCallCount());
        self::assertNull($result->last(HandledStamp::class));
        $stamp = $result->last(LockNotAcquiredStamp::class);
        self::assertInstanceOf(LockNotAcquiredStamp::class, $stamp);
        self::assertSame(self::RESOURCE, $stamp->getResource());
        self::assertSame(600.0, $stamp->getTtl());
        $records = $this->logger->getRecords();
        self::assertCount(1, $records);
        self::assertSame(LogLevel::ERROR, $records[0]['level']);
        self::assertSame([
            'class' => WithLockDataMessageStub::class,
            'message_id' => 'message-id',
            'resource' => self::RESOURCE,
            'transport' => 'async',
            'ttl' => 600.0,
        ], $records[0]['context']);

        $lock->release();
    }

    public function testItDropsMessageAndLogsWithDefaultsWhenLockNotAcquiredAndNoTransportStamps(): void
    {
        $lock = $this->locker->createLock(self::RESOURCE);
        $lock->acquire();
        $envelope = new Envelope(new stdClass(), [new ConsumedByWorkerStamp(), new WithLockDataStamp(self::RESOURCE)]);

        $result = $this->createMiddleware()
            ->handle($envelope, new StackMiddleware($this->handler));

        self::assertSame(0, $this->handler->getHandleCallCount());
        self::assertInstanceOf(LockNotAcquiredStamp::class, $result->last(LockNotAcquiredStamp::class));
        $records = $this->logger->getRecords();
        self::assertCount(1, $records);
        self::assertSame(LogLevel::WARNING, $records[0]['level']);
        self::assertSame([
            'class' => stdClass::class,
            'message_id' => null,
            'resource' => self::RESOURCE,
            'transport' => null,
            'ttl' => LockData::DEFAULT_TTL,
        ], $records[0]['context']);

        $lock->release();
    }

    #[DataProvider('provideLockedEnvelopes')]
    public function testItHandlesMessageWhenLockAcquired(Envelope $envelope): void
    {
        $result = $this->createMiddleware()
            ->handle($envelope, new StackMiddleware($this->handler));

        self::assertSame(1, $this->handler->getHandleCallCount());
        self::assertNotNull($result->last(HandledStamp::class));
        self::assertNull($result->last(LockNotAcquiredStamp::class));
        self::assertSame([], $this->logger->getRecords());
        // Lock is released after handling
        self::assertTrue($this->locker->createLock(self::RESOURCE)->acquire());
    }

    public function testItThrowsShouldRetryExceptionWhenLockNotAcquiredAndRetryEnabled(): void
    {
        $lock = $this->locker->createLock(self::RESOURCE);
        $lock->acquire();
        $envelope = new Envelope(
            new WithLockDataMessageStub(LockData::create(self::RESOURCE, retry: true)),
            [new ConsumedByWorkerStamp()]
        );

        try {
            $this->createMiddleware()
                ->handle($envelope, new StackMiddleware($this->handler));

            self::fail(\sprintf('%s was not thrown', ShouldRetryException::class));
        } catch (ShouldRetryException) {
            self::assertSame(0, $this->handler->getHandleCallCount());
            self::assertSame([], $this->logger->getRecords());
        } finally {
            $lock->release();
        }
    }

    private function createMiddleware(?string $logLevel = null): ProcessWithLockMiddleware
    {
        $middleware = $logLevel === null
            ? new ProcessWithLockMiddleware($this->logger)
            : new ProcessWithLockMiddleware($this->logger, $logLevel);
        $middleware->setLocker($this->locker);

        return $middleware;
    }
}
