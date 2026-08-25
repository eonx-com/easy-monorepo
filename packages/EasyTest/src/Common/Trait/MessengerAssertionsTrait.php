<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use DateTimeImmutable;
use PHPUnit\Framework\Constraint\IsEqual;
use ReflectionProperty;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait MessengerAssertionsTrait
{
    private const string ASYNC_TRANSPORT_NAME = 'async';

    private const float CLOCK_OVERSHOOT_IN_SECONDS = 0.000001;

    private const string FAILED_TRANSPORT_NAME = 'failed';

    private const int MAX_WORKER_RUNS = 100;

    /**
     * @param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToAsyncTransport(int $count, ?string $messageClass = null): void
    {
        self::assertCount($count, self::getMessagesSentToTransport(self::ASYNC_TRANSPORT_NAME, $messageClass));
    }

    /**
     * @param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToFailedTransport(int $count, ?string $messageClass = null): void
    {
        self::assertCount($count, self::getMessagesSentToTransport(self::FAILED_TRANSPORT_NAME, $messageClass));
    }

    /**
     * @param class-string $messageClass
     */
    public static function assertMessageSentToAsyncTransport(
        string $messageClass,
        array $expectedProperties = [],
        int $messagesCount = 1,
    ): void {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $envelopes = \array_filter(
            self::getMessagesSentToTransport(self::ASYNC_TRANSPORT_NAME),
            static function (object $message) use ($messageClass, $expectedProperties, $propertyAccessor): bool {
                if ($message instanceof $messageClass === false) {
                    return false;
                }

                foreach ($expectedProperties as $property => $expectedValue) {
                    $actualValue = $propertyAccessor->getValue($message, $property);

                    $isEqualConstraint = new IsEqual($expectedValue);
                    if ($isEqualConstraint->evaluate($actualValue, '', true) === false) {
                        return false;
                    }
                }

                return true;
            }
        );

        self::assertCount($messagesCount, $envelopes);
    }

    /**
     * @param array<class-string<\Throwable>, int|string|null> $expectedFailures
     */
    public static function consumeAsyncMessages(array $expectedFailures = []): void
    {
        $transport = self::getTransport(self::ASYNC_TRANSPORT_NAME);
        $alreadyRejectedCount = \count($transport->getRejected());

        self::runMessengerWorker($transport);
        self::assertMessageFailures(
            $expectedFailures,
            self::getMessageFailures($transport, $alreadyRejectedCount)
        );
    }

    /**
     * @template TMessageClass
     *
     * @param class-string<TMessageClass>|null $messageClass
     *
     * @return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     */
    public static function getMessagesSentToTransport(string $transportName, ?string $messageClass = null): array
    {
        $messages = [];

        foreach (self::getTransport($transportName)->getSent() as $envelope) {
            $message = $envelope->getMessage();

            if ($messageClass === null || $message instanceof $messageClass) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    private static function advanceClockToNextDelayedMessage(
        InMemoryTransport $transport,
        ClockInterface $clock,
    ): bool {
        /** @var array<\DateTimeImmutable> $availableAt */
        $availableAt = new ReflectionProperty(InMemoryTransport::class, 'availableAt')->getValue($transport);

        if ($availableAt === []) {
            return false;
        }

        if ($clock instanceof MockClock === false) {
            self::fail(\sprintf(
                'Messages are waiting on a delay, but the clock is not mocked, so waiting the delays out would'
                . ' really sleep. Run the test on a mock clock, e.g. via %s::mockTime().',
                ClockSensitiveTrait::class
            ));
        }

        $dueAtTimestamps = \array_map(
            static fn(DateTimeImmutable $dueAt): float => (float)$dueAt->format('U.u'),
            \array_values($availableAt)
        );
        $nowTimestamp = (float)$clock->now()
            ->format('U.u');

        $clock->sleep(\max(0, \min($dueAtTimestamps) - $nowTimestamp) + self::CLOCK_OVERSHOOT_IN_SECONDS);

        if (self::countAvailableMessages($transport) === 0) {
            self::fail(
                'The clock was advanced past the next delay, but no message became available. The messenger'
                . ' transport computes availability from the container "clock" service, while the test advanced'
                . ' the global clock - make both use the same mock clock.'
            );
        }

        return true;
    }

    private static function assertExpectedFailuresShape(array $expectedFailures): void
    {
        foreach (\array_keys($expectedFailures) as $exceptionClass) {
            if (\is_string($exceptionClass) === false) {
                self::fail(\sprintf(
                    'Expected failures must map an exception class to a code, or to null to accept any code,'
                    . ' %s given as a key. The [SomeException::class] and [[SomeException::class => 42]] formats'
                    . ' of EasyTest 6.x are no longer supported. The bare-class format matched only code 0, so'
                    . ' its exact replacement is [SomeException::class => 0].',
                    \var_export($exceptionClass, true)
                ));
            }
        }
    }

    /**
     * @param array<class-string<\Throwable>, int|string|null> $expectedFailures
     * @param \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp[] $actualFailures
     */
    private static function assertMessageFailures(array $expectedFailures, array $actualFailures): void
    {
        self::assertExpectedFailuresShape($expectedFailures);

        $missingFailures = $expectedFailures;

        foreach ($actualFailures as $actualFailure) {
            $exceptionClass = $actualFailure->getExceptionClass();

            if (\array_key_exists($exceptionClass, $expectedFailures) === false) {
                self::fail(\sprintf(
                    "Unexpected exception thrown while handling a message.\n\n%s\n\nExpected exceptions:\n%s",
                    self::describeFailure($actualFailure),
                    self::describeExpectedFailures($expectedFailures)
                ));
            }

            $expectedCode = $expectedFailures[$exceptionClass];

            if ($expectedCode !== null && $actualFailure->getExceptionCode() !== $expectedCode) {
                self::fail(\sprintf(
                    "Exception thrown while handling a message carries an unexpected code, %s expected.\n\n%s",
                    \var_export($expectedCode, true),
                    self::describeFailure($actualFailure)
                ));
            }

            unset($missingFailures[$exceptionClass]);
        }

        self::assertSame([], \array_keys($missingFailures), \sprintf(
            "The following exceptions were expected but never thrown:\n%s",
            self::describeExpectedFailures($missingFailures)
        ));
    }

    private static function countAvailableMessages(InMemoryTransport $transport): int
    {
        return \iterator_count($transport->get(\PHP_INT_MAX));
    }

    /**
     * @param array<class-string<\Throwable>, int|string|null> $expectedFailures
     */
    private static function describeExpectedFailures(array $expectedFailures): string
    {
        if ($expectedFailures === []) {
            return ' - None';
        }

        $descriptions = [];
        foreach ($expectedFailures as $exceptionClass => $exceptionCode) {
            $descriptions[] = $exceptionCode === null
                ? ' - ' . $exceptionClass
                : \sprintf(' - %s (code: %s)', $exceptionClass, \var_export($exceptionCode, true));
        }

        return \implode(\PHP_EOL, $descriptions);
    }

    private static function describeFailure(ErrorDetailsStamp $errorDetailsStamp): string
    {
        $description = \sprintf(
            '%s (code: %s): %s',
            $errorDetailsStamp->getExceptionClass(),
            \var_export($errorDetailsStamp->getExceptionCode(), true),
            $errorDetailsStamp->getExceptionMessage()
        );

        $flattenException = $errorDetailsStamp->getFlattenException();

        if ($flattenException === null) {
            return $description . \PHP_EOL . \PHP_EOL . 'Stack trace:' . \PHP_EOL . 'No stack trace available.';
        }

        return $description . \PHP_EOL . \PHP_EOL . $flattenException->getAsString();
    }

    /**
     * @return \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp[]
     */
    private static function getMessageFailures(InMemoryTransport $transport, int $skipRejectedCount = 0): array
    {
        $messageFailures = [];

        foreach (\array_slice($transport->getRejected(), $skipRejectedCount) as $envelope) {
            $errorDetailsStamp = $envelope->last(ErrorDetailsStamp::class);

            if ($errorDetailsStamp !== null) {
                $messageFailures[] = $errorDetailsStamp;
            }
        }

        return $messageFailures;
    }

    private static function getTransport(string $transportName): InMemoryTransport
    {
        $transport = self::getContainer()->get('messenger.transport.' . $transportName);

        if ($transport instanceof InMemoryTransport === false) {
            self::fail(\sprintf(
                'The "%s" transport must use an in-memory DSN in the test environment, got %s.',
                $transportName,
                \get_debug_type($transport)
            ));
        }

        return $transport;
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    private static function getUnhandledEnvelopes(InMemoryTransport $transport): array
    {
        $sentEnvelopes = $transport->getSent();
        $handledCount = \count($transport->getAcknowledged()) + \count($transport->getRejected());

        if (\count($sentEnvelopes) === $handledCount) {
            return [];
        }

        $handledIds = [];
        foreach ([...$transport->getAcknowledged(), ...$transport->getRejected()] as $envelope) {
            $id = $envelope->last(TransportMessageIdStamp::class)
                ?->getId();

            if (\is_int($id) || \is_string($id)) {
                $handledIds[$id] = true;
            }
        }

        $unhandledEnvelopes = [];
        foreach ($sentEnvelopes as $envelope) {
            $id = $envelope->last(TransportMessageIdStamp::class)
                ?->getId();

            if ((\is_int($id) || \is_string($id)) && isset($handledIds[$id]) === false) {
                $unhandledEnvelopes[] = $envelope;
            }
        }

        return $unhandledEnvelopes;
    }

    private static function runMessengerWorker(InMemoryTransport $transport): void
    {
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        /** @var \Symfony\Component\Messenger\MessageBusInterface $messageBus */
        $messageBus = self::getContainer()->get('messenger.routable_message_bus');
        /** @var \Symfony\Component\Messenger\EventListener\ResetServicesListener $resetServicesListener */
        $resetServicesListener = self::getContainer()->get('messenger.listener.reset_services');

        $stopWorkerOnIdleListener = static function (WorkerRunningEvent $event): void {
            if ($event->isWorkerIdle()) {
                $event->getWorker()
                    ->stop();
            }
        };
        $eventDispatcher->addListener(WorkerRunningEvent::class, $stopWorkerOnIdleListener);
        $eventDispatcher->addSubscriber($resetServicesListener);

        $sentCountBeforeRun = \count($transport->getSent());
        $clock = Clock::get();
        $messageLimitListener = null;

        $run = 0;

        try {
            while ($run < self::MAX_WORKER_RUNS) {
                $availableMessagesCount = self::countAvailableMessages($transport);

                if ($availableMessagesCount === 0) {
                    $retryIsDue = self::advanceClockToNextDelayedMessage($transport, $clock);

                    if ($retryIsDue === false) {
                        break;
                    }

                    continue;
                }

                $run++;

                if ($messageLimitListener !== null) {
                    $eventDispatcher->removeSubscriber($messageLimitListener);
                }

                $messageLimitListener = new StopWorkerOnMessageLimitListener($availableMessagesCount);
                $eventDispatcher->addSubscriber($messageLimitListener);

                $worker = new Worker(
                    [self::ASYNC_TRANSPORT_NAME => $transport],
                    $messageBus,
                    $eventDispatcher,
                    clock: $clock
                );
                $worker->run(['fetch_size' => $availableMessagesCount]);
            }
        } finally {
            $eventDispatcher->removeListener(WorkerRunningEvent::class, $stopWorkerOnIdleListener);
            $eventDispatcher->removeSubscriber($resetServicesListener);

            if ($messageLimitListener !== null) {
                $eventDispatcher->removeSubscriber($messageLimitListener);
            }
        }

        if (\count($transport->getSent()) < $sentCountBeforeRun) {
            self::fail(
                'The in-memory transport was reset while consuming messages, so messages were silently lost.'
                . ' The test transport must survive service resets, e.g. via a transport factory that keeps'
                . ' its transports across resets.'
            );
        }

        $leftoverMessageClasses = [];
        foreach (self::getUnhandledEnvelopes($transport) as $envelope) {
            $leftoverMessageClasses[] = $envelope->getMessage()::class;
        }

        self::assertSame([], $leftoverMessageClasses, \sprintf(
            'Unable to consume all messages from the "%s" transport within %d worker runs. Either a handler'
            . ' keeps dispatching new messages forever, or the workload needs more waves than the budget allows.',
            self::ASYNC_TRANSPORT_NAME,
            self::MAX_WORKER_RUNS
        ));
    }
}
