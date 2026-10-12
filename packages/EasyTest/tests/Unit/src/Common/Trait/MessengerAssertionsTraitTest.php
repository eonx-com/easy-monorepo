<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Common\Trait;

use ArrayObject;
use EonX\EasyTest\Common\Trait\MessengerAssertionsTrait;
use EonX\EasyTest\Tests\Fixture\Message\DummyMessage;
use EonX\EasyTest\Tests\Fixture\Message\FailingDummyMessage;
use LogicException;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;

final class MessengerAssertionsTraitTest extends KernelTestCase
{
    use ClockSensitiveTrait;
    use MessengerAssertionsTrait;

    private static ?Container $containerStub = null;

    protected function tearDown(): void
    {
        self::$containerStub = null;

        parent::tearDown();
    }

    public function testItAcceptsAnyCodeForNullInTheExactList(): void
    {
        $expectedFailures = [
            [RuntimeException::class => 42],
            [RuntimeException::class => null],
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 999, 'Any code goes'),
            new ErrorDetailsStamp(RuntimeException::class, 42, 'Exact code'),
        ];

        $this->expectNotToPerformAssertions();

        self::assertMessageFailuresExactly($expectedFailures, $actualFailures);
    }

    public function testItComparesClockAdvancesAtMillisecondPrecision(): void
    {
        self::assertClockAdvances([1, 30.5], [1.0000004, 30.499999]);
    }

    public function testItConsumesWithExactFailuresDelaysAndTransportsTogether(): void
    {
        $clock = self::mockTime('2035-01-01 00:00:00');
        [$messageBus, $handledLog] = $this->arrangeTwoTransportMessenger($clock);
        $messageBus->dispatch(new DummyMessage());

        self::consumeAsyncMessages(
            [
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
            ],
            expectedDelays: [1, 2, 4],
            transportNames: ['async', 'email_delivery'],
        );

        $handledCounts = \array_count_values(\iterator_to_array($handledLog));
        self::assertSame(1, $handledCounts[DummyMessage::class]);
        self::assertSame(4, $handledCounts[FailingDummyMessage::class]);
        self::assertSame('2035-01-01 00:00:07', $clock->now()->format('Y-m-d H:i:s'));
        self::assertCount(4, self::getMessagesSentToTransport('email_delivery', FailingDummyMessage::class));
        self::assertCountOfMessagesSentToAsyncTransport(1);
    }

    public function testItCountsAvailableMessagesAcrossAllTransports(): void
    {
        $asyncTransport = new InMemoryTransport();
        $asyncTransport->send(new Envelope(new DummyMessage('first')));
        $asyncTransport->send(new Envelope(new DummyMessage('second')));
        $emailTransport = new InMemoryTransport();
        $emailTransport->send(new Envelope(new DummyMessage('third')));

        $count = self::countAvailableMessages(['async' => $asyncTransport, 'email_delivery' => $emailTransport]);

        self::assertSame(3, $count);
    }

    public function testItCountsEveryAvailableMessageNotJustTheFirst(): void
    {
        $transport = new InMemoryTransport();
        $transport->send(new Envelope(new DummyMessage('first')));
        $transport->send(new Envelope(new DummyMessage('second')));
        $transport->send(new Envelope(new DummyMessage('third')));

        $count = self::countAvailableMessages(['async' => $transport]);

        self::assertSame(3, $count);
    }

    public function testItDoesNotCountMessagesWaitingOnADelay(): void
    {
        $clock = new MockClock();
        $transport = new InMemoryTransport(null, $clock);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));

        $count = self::countAvailableMessages(['async' => $transport]);

        self::assertSame(0, $count);
        self::assertCount(1, self::getUnhandledEnvelopes($transport));
    }

    public function testItFailsWhenATransportIsResetWhileConsuming(): void
    {
        [$messageBus] = $this->arrangeTwoTransportMessenger(self::mockTime());
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        /** @var \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport $asyncTransport */
        $asyncTransport = self::getContainer()->get('messenger.transport.async');
        $eventDispatcher->addListener(WorkerRunningEvent::class, static function () use ($asyncTransport): void {
            $asyncTransport->reset();
        });
        $messageBus->dispatch(new DummyMessage());

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('The "async" in-memory transport was reset while consuming messages');

        self::consumeAsyncMessages(transportNames: ['async', 'email_delivery']);
    }

    public function testItFailsWhenATransportThatStartedEmptyIsResetWhileConsuming(): void
    {
        [$messageBus] = $this->arrangeTwoTransportMessenger(self::mockTime());
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        /** @var \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport $emailTransport */
        $emailTransport = self::getContainer()->get('messenger.transport.email_delivery');
        $eventDispatcher->addListener(WorkerRunningEvent::class, static function () use ($emailTransport): void {
            $emailTransport->reset();
        });
        $messageBus->dispatch(new DummyMessage());

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('The "email_delivery" in-memory transport was reset while consuming messages');

        self::consumeAsyncMessages(transportNames: ['async', 'email_delivery']);
    }

    public function testItFailsWhenBareClassesAndPairsAreMixedInOneList(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('either all bare exception classes');

        self::assertExpectedFailuresShape([RuntimeException::class, [LogicException::class => 42]]);
    }

    public function testItFailsWhenClockAdvancesDoNotMatchExpectedDelays(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            'expected to move the clock by [10, 1, 30, 60] second(s), but it moved by [1, 30, 60]'
        );

        self::assertClockAdvances([10, 1, 30, 60], [1.0, 30.0, 60.0]);
    }

    public function testItFailsWhenExactListEntryIsNotASinglePairMap(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('an array of 2 entries given at index 0');

        self::assertExpectedFailuresShape([[RuntimeException::class => 42, LogicException::class => 7]]);
    }

    public function testItFailsWhenExactListHasFewerFailuresThanThrown(): void
    {
        $expectedFailures = [
            [RuntimeException::class => 42],
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 42, 'Attempt 1'),
            new ErrorDetailsStamp(RuntimeException::class, 42, 'Attempt 2'),
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Thrown but not expected');

        self::assertMessageFailuresExactly($expectedFailures, $actualFailures);
    }

    public function testItFailsWhenExactListHasMoreFailuresThanThrown(): void
    {
        $expectedFailures = [
            [RuntimeException::class => 42],
            [RuntimeException::class => 42],
        ];
        $actualFailures = [new ErrorDetailsStamp(RuntimeException::class, 42, 'Attempt 1')];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Expected but never thrown:\n - RuntimeException (code: 42)");

        self::assertMessageFailuresExactly($expectedFailures, $actualFailures);
    }

    public function testItFailsWhenExpectedCodeHasAnInvalidTypeInTheExactList(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('An expected exception code must be an integer, a string, or null');

        self::assertExpectedFailuresShape([[RuntimeException::class => ['not', 'a', 'code']]]);
    }

    public function testItFailsWhenExpectedCodeHasAnInvalidTypeInTheMap(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('An expected exception code must be an integer, a string, or null');

        self::assertExpectedFailuresShape([RuntimeException::class => 1.5]);
    }

    public function testItFailsWhenExpectedDelaysAreNegative(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('non-negative numbers of seconds, -1 given');

        self::assertExpectedDelaysShape([10, -1]);
    }

    public function testItFailsWhenExpectedDelaysAreNotAList(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('must be a list of seconds');

        self::assertExpectedDelaysShape(['first' => 10]);
    }

    public function testItFailsWhenExpectedDelaysAreNotFinite(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('non-negative numbers of seconds');

        self::assertExpectedDelaysShape([10, \NAN]);
    }

    public function testItFailsWhenExpectedExceptionCarriesAnotherCode(): void
    {
        $expectedFailures = [
            RuntimeException::class => 42,
        ];
        $actualFailures = [new ErrorDetailsStamp(RuntimeException::class, 13, 'Something went wrong')];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('carries an unexpected code, 42 expected');

        self::assertMessageFailures($expectedFailures, $actualFailures);
    }

    public function testItFailsWhenExpectedExceptionWasNeverThrown(): void
    {
        $expectedFailures = [
            RuntimeException::class => null,
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("The following exceptions were expected but never thrown:\n - RuntimeException");

        self::assertMessageFailures($expectedFailures, []);
    }

    public function testItFailsWhenMapAndListShapesAreMixed(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Mixing the two shapes is not supported');

        self::assertExpectedFailuresShape([LogicException::class => 42, RuntimeException::class]);
    }

    public function testItFailsWhenMessagesAreDelayedButTheClockIsNotMocked(): void
    {
        $transport = new InMemoryTransport();
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('the clock is not mocked');

        self::advanceClockToNextDelayedMessage(['async' => $transport], new NativeClock());
    }

    public function testItFailsWhenNoTransportNamesAreGiven(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('At least one transport name is required');

        self::consumeAsyncMessages(transportNames: []);
    }

    public function testItFailsWhenTheFailedTransportIsListed(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('The "failed" transport must not be consumed');

        self::consumeAsyncMessages(transportNames: ['async', 'failed']);
    }

    public function testItFailsWhenTransportRunsOnAnotherClockThanTheTest(): void
    {
        $transportClock = new MockClock();
        $transport = new InMemoryTransport(null, $transportClock);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('no message became available');

        self::advanceClockToNextDelayedMessage(['async' => $transport], new MockClock('2020-01-01 00:00:00'));
    }

    public function testItFailsWhenTwoTransportNamesResolveToTheSameInstance(): void
    {
        $this->arrangeTwoTransportMessenger(new MockClock());
        self::$containerStub?->set(
            'messenger.transport.async_alias',
            self::$containerStub->get('messenger.transport.async')
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('resolves to the same transport instance');

        self::consumeAsyncMessages(transportNames: ['async', 'async_alias']);
    }

    public function testItFailsWhenUnexpectedExceptionWasThrown(): void
    {
        $actualFailures = [new ErrorDetailsStamp(RuntimeException::class, 0, 'Something went wrong')];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('RuntimeException (code: 0): Something went wrong');

        self::assertMessageFailures([], $actualFailures);
    }

    public function testItFailsWhenUnexpectedExceptionWasThrownAlongsideExpectedOne(): void
    {
        $expectedFailures = [
            RuntimeException::class => null,
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 0, 'Expected'),
            new ErrorDetailsStamp(LogicException::class, 0, 'Unexpected'),
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('LogicException (code: 0): Unexpected');

        self::assertMessageFailures($expectedFailures, $actualFailures);
    }

    public function testItMakesADelayedMessageAvailableByMovingTheClock(): void
    {
        $clock = new MockClock();
        $transport = new InMemoryTransport(null, $clock);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));

        $advanced = self::advanceClockToNextDelayedMessage(['async' => $transport], $clock);

        self::assertEqualsWithDelta(10.0, $advanced, 0.001);
        self::assertSame(1, self::countAvailableMessages(['async' => $transport]));
    }

    public function testItMakesAZeroDelayMessageAvailableByMovingTheClock(): void
    {
        $clock = new MockClock();
        $transport = new InMemoryTransport(null, $clock);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(0)]));

        $advanced = self::advanceClockToNextDelayedMessage(['async' => $transport], $clock);

        self::assertEqualsWithDelta(0.0, $advanced, 0.001);
        self::assertSame(1, self::countAvailableMessages(['async' => $transport]));
    }

    public function testItMatchesTheExactFailureListRegardlessOfOrder(): void
    {
        $expectedFailures = [
            [LogicException::class => 7],
            [RuntimeException::class => 42],
            [RuntimeException::class => 42],
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 42, 'First'),
            new ErrorDetailsStamp(LogicException::class, 7, 'Second'),
            new ErrorDetailsStamp(RuntimeException::class, 42, 'Third'),
        ];

        $this->expectNotToPerformAssertions();

        self::assertMessageFailuresExactly($expectedFailures, $actualFailures);
    }

    public function testItMovesTheClockExactlyToTheDueTimeOfTheDelayedMessage(): void
    {
        $clock = new MockClock();
        $startTimestamp = (float)$clock->now()
            ->format('U.u');
        $transport = new InMemoryTransport(null, $clock);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));
        $clock->sleep(3);

        self::advanceClockToNextDelayedMessage(['async' => $transport], $clock);

        $nowTimestamp = (float)$clock->now()
            ->format('U.u');
        self::assertEqualsWithDelta(10.0, $nowTimestamp - $startTimestamp, 0.001);
        self::assertSame(1, self::countAvailableMessages(['async' => $transport]));
    }

    public function testItMovesTheClockToTheShortestDelayAcrossAllTransports(): void
    {
        $clock = new MockClock();
        $asyncTransport = new InMemoryTransport(null, $clock);
        $asyncTransport->send(new Envelope(new DummyMessage('later'), [new DelayStamp(640_000)]));
        $emailTransport = new InMemoryTransport(null, $clock);
        $emailTransport->send(new Envelope(new DummyMessage('soon'), [new DelayStamp(10_000)]));

        $advanced = self::advanceClockToNextDelayedMessage(
            ['async' => $asyncTransport, 'email_delivery' => $emailTransport],
            $clock
        );

        self::assertEqualsWithDelta(10.0, $advanced, 0.001);
        self::assertSame(0, self::countAvailableMessages(['async' => $asyncTransport]));
        self::assertSame(1, self::countAvailableMessages(['email_delivery' => $emailTransport]));
    }

    public function testItMovesTheClockToTheShortestDelayOnly(): void
    {
        $clock = new MockClock();
        $transport = new InMemoryTransport(null, $clock);
        $transport->send(new Envelope(new DummyMessage('soon'), [new DelayStamp(10_000)]));
        $transport->send(new Envelope(new DummyMessage('later'), [new DelayStamp(640_000)]));

        self::advanceClockToNextDelayedMessage(['async' => $transport], $clock);

        self::assertSame(1, self::countAvailableMessages(['async' => $transport]));
    }

    public function testItReportsNothingToWaitForWhenTransportIsDrained(): void
    {
        $transport = new InMemoryTransport();
        $envelope = $transport->send(new Envelope(new DummyMessage()));
        $transport->ack($envelope);

        $advanced = self::advanceClockToNextDelayedMessage(['async' => $transport], new MockClock());

        self::assertNull($advanced);
        self::assertSame([], self::getUnhandledEnvelopes($transport));
    }

    public function testItReportsNothingToWaitForWhenUnhandledMessageHasNoDelay(): void
    {
        $transport = new InMemoryTransport();
        $transport->send(new Envelope(new DummyMessage()));

        $advanced = self::advanceClockToNextDelayedMessage(['async' => $transport], new MockClock());

        self::assertNull($advanced);
    }

    public function testItReportsOneFailurePerDeliveryAttempt(): void
    {
        $transport = new InMemoryTransport();
        $firstException = new RuntimeException('Attempt 1', 42);
        $firstAttempt = $transport->send(new Envelope(new DummyMessage(), [
            ErrorDetailsStamp::create($firstException),
        ]));
        $secondAttempt = $transport->send(new Envelope(new DummyMessage(), [
            ErrorDetailsStamp::create($firstException),
            ErrorDetailsStamp::create(new RuntimeException('Attempt 2', 42)),
        ]));
        $transport->reject($firstAttempt);
        $transport->reject($secondAttempt);

        $messageFailures = self::getMessageFailures($transport);

        self::assertCount(2, $messageFailures);
        self::assertSame(RuntimeException::class, $messageFailures[0]->getExceptionClass());
        self::assertSame(42, $messageFailures[0]->getExceptionCode());
        self::assertSame('Attempt 1', $messageFailures[0]->getExceptionMessage());
        $flattenException = $messageFailures[0]->getFlattenException();
        self::assertNotNull($flattenException);
        self::assertSame($firstException->getFile(), $flattenException->getFile());
        self::assertSame($firstException->getLine(), $flattenException->getLine());
        self::assertSame('Attempt 2', $messageFailures[1]->getExceptionMessage());
    }

    public function testItReportsStackTraceOfUnexpectedException(): void
    {
        $actualFailures = [ErrorDetailsStamp::create(new RuntimeException('Boom'))];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Stack trace:\n#0 ");

        self::assertMessageFailures([], $actualFailures);
    }

    public function testItReportsThePreviousExceptionChainOfUnexpectedException(): void
    {
        $rootCause = new LogicException('The actual root cause');
        $actualFailures = [ErrorDetailsStamp::create(new RuntimeException('Wrapper', 0, $rootCause))];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('The actual root cause');

        self::assertMessageFailures([], $actualFailures);
    }

    public function testItReportsUnexpectedFailuresWithTheLenientWordingOnBareCalls(): void
    {
        $this->arrangeTwoTransportMessenger(self::mockTime());
        /** @var \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport $asyncTransport */
        $asyncTransport = self::getContainer()->get('messenger.transport.async');
        $asyncTransport->send(new Envelope(new FailingDummyMessage()));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected exception thrown while handling a message');

        self::consumeAsyncMessages();
    }

    public function testItSkipsTheGivenCountOfRejectedEnvelopes(): void
    {
        $transport = new InMemoryTransport();
        $staleAttempt = $transport->send(new Envelope(new DummyMessage(), [
            ErrorDetailsStamp::create(new RuntimeException('Stale', 42)),
        ]));
        $freshAttempt = $transport->send(new Envelope(new DummyMessage(), [
            ErrorDetailsStamp::create(new RuntimeException('Fresh', 42)),
        ]));
        $transport->reject($staleAttempt);
        $transport->reject($freshAttempt);

        $messageFailures = self::getMessageFailures($transport, 1);

        self::assertCount(1, $messageFailures);
        self::assertSame('Fresh', $messageFailures[0]->getExceptionMessage());
    }

    public function testItSucceedsWhenExactListMatchesEveryAttempt(): void
    {
        $expectedFailures = [
            [RuntimeException::class => 13603],
            [RuntimeException::class => 13603],
            [RuntimeException::class => 13603],
            [RuntimeException::class => 13603],
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 1'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 2'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 3'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 4'),
        ];

        $this->expectNotToPerformAssertions();

        self::assertMessageFailuresExactly($expectedFailures, $actualFailures);
    }

    public function testItSucceedsWhenNothingFailedAndNothingWasExpected(): void
    {
        $actualFailures = [];

        self::assertMessageFailures([], $actualFailures);
    }

    public function testItSucceedsWhenTheSameExceptionWasThrownByEveryRetry(): void
    {
        $expectedFailures = [
            RuntimeException::class => 13603,
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 1'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 2'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 3'),
            new ErrorDetailsStamp(RuntimeException::class, 13603, 'Attempt 4'),
        ];

        self::assertMessageFailures($expectedFailures, $actualFailures);
    }

    public function testItSucceedsWhenTwoDifferentExceptionsWereExpected(): void
    {
        $expectedFailures = [
            LogicException::class => null,
            RuntimeException::class => 42,
        ];
        $actualFailures = [
            new ErrorDetailsStamp(RuntimeException::class, 42, 'First'),
            new ErrorDetailsStamp(LogicException::class, 7, 'Second'),
        ];

        self::assertMessageFailures($expectedFailures, $actualFailures);
    }

    public function testItSucceedsWithoutCheckingCodeWhenNullExpected(): void
    {
        $expectedFailures = [
            RuntimeException::class => null,
        ];
        $actualFailures = [new ErrorDetailsStamp(RuntimeException::class, 999, 'Any code goes')];

        self::assertMessageFailures($expectedFailures, $actualFailures);
    }

    public function testItTreatsRejectedMessagesAsHandled(): void
    {
        $transport = new InMemoryTransport();
        $acknowledged = $transport->send(new Envelope(new DummyMessage('acknowledged')));
        $rejected = $transport->send(new Envelope(new DummyMessage('rejected')));
        $transport->send(new Envelope(new DummyMessage('still waiting')));
        $transport->ack($acknowledged);
        $transport->reject($rejected);

        $unhandledEnvelopes = self::getUnhandledEnvelopes($transport);

        self::assertCount(1, $unhandledEnvelopes);
        /** @var \EonX\EasyTest\Tests\Fixture\Message\DummyMessage $message */
        $message = $unhandledEnvelopes[0]->getMessage();
        self::assertSame('still waiting', $message->getName());
    }

    protected static function getContainer(): Container
    {
        return self::$containerStub ?? parent::getContainer();
    }

    /**
     * @return array{0: \Symfony\Component\Messenger\MessageBus, 1: \ArrayObject<int, string>}
     */
    private function arrangeTwoTransportMessenger(ClockInterface $clock): array
    {
        $asyncTransport = new InMemoryTransport(null, $clock);
        $emailTransport = new InMemoryTransport(null, $clock);
        /** @var \ArrayObject<int, string> $handledLog */
        $handledLog = new ArrayObject();
        /** @var \Symfony\Component\Messenger\MessageBus|null $messageBus */
        $messageBus = null;

        $handlersLocator = new HandlersLocator([
            DummyMessage::class => [static function (DummyMessage $message) use ($handledLog, &$messageBus): void {
                $handledLog->append($message::class);
                $messageBus?->dispatch(new FailingDummyMessage());
            }],
            FailingDummyMessage::class => [static function (FailingDummyMessage $message) use ($handledLog): void {
                $handledLog->append($message::class);

                throw new RuntimeException('boom', 77);
            }],
        ]);

        $sendersContainer = new Container();
        $sendersContainer->set('async', $asyncTransport);
        $sendersContainer->set('email_delivery', $emailTransport);
        $sendersLocator = new SendersLocator([
            DummyMessage::class => ['async'],
            FailingDummyMessage::class => ['email_delivery'],
        ], $sendersContainer);

        $messageBus = new MessageBus([
            new SendMessageMiddleware($sendersLocator),
            new HandleMessageMiddleware($handlersLocator),
        ]);

        $retryStrategyContainer = new Container();
        $retryStrategyContainer->set('async', new MultiplierRetryStrategy(3, 1000, 2, 0, 0));
        $retryStrategyContainer->set('email_delivery', new MultiplierRetryStrategy(3, 1000, 2, 0, 0));

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new AddErrorDetailsStampListener());
        $eventDispatcher->addSubscriber(
            new SendFailedMessageForRetryListener($sendersContainer, $retryStrategyContainer)
        );

        self::$containerStub = new Container();
        self::$containerStub->set('messenger.transport.async', $asyncTransport);
        self::$containerStub->set('messenger.transport.email_delivery', $emailTransport);
        self::$containerStub->set(
            'messenger.routable_message_bus',
            new RoutableMessageBus(new Container(), $messageBus)
        );
        self::$containerStub->set(EventDispatcherInterface::class, $eventDispatcher);
        self::$containerStub->set('messenger.listener.reset_services', new class implements EventSubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return [];
            }
        });

        return [$messageBus, $handledLog];
    }
}
