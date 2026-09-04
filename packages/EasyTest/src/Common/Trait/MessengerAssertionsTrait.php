<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

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
     * Transports are polled in the listed order, earlier ones first, like messenger:consume.
     *
     * Expected failures accept three spellings:
     * - [SomeException::class => 42]: every listed exception must be thrown at least once and nothing
     *   else may fail; how many times it was thrown is not asserted
     * - [SomeException::class]: same as [SomeException::class => 0]
     * - [[SomeException::class => 42], [SomeException::class => 42]]: the thrown exceptions must match
     *   the list exactly, count included
     * A null code accepts any code; bare classes and single-pair maps cannot be mixed in one list.
     *
     * Expected delays are the exact clock moves, in seconds, the consuming must perform to deliver
     * delayed messages, e.g. [10, 1, 30, 60]; null skips the check, [] asserts the clock never moved.
     *
     * @param array<class-string<\Throwable>, int|string|null>|list<class-string<\Throwable>|array<class-string<\Throwable>, int|string|null>> $expectedFailures
     * @param list<int|float>|null $expectedDelays
     * @param string[] $transportNames
     */
    public static function consumeAsyncMessages(
        array $expectedFailures = [],
        ?array $expectedDelays = null,
        array $transportNames = [self::ASYNC_TRANSPORT_NAME],
    ): void {
        self::assertNotSame([], $transportNames, 'At least one transport name is required to consume messages.');

        if (\in_array(self::FAILED_TRANSPORT_NAME, $transportNames, true)) {
            self::fail(\sprintf(
                'The "%s" transport must not be consumed - it would re-handle dead-lettered messages. Assert its'
                . ' content instead, e.g. via assertCountOfMessagesSentToFailedTransport().',
                self::FAILED_TRANSPORT_NAME
            ));
        }

        self::assertExpectedFailuresShape($expectedFailures);
        self::assertExpectedDelaysShape($expectedDelays);

        $isBareClassList = \array_is_list($expectedFailures)
            && $expectedFailures !== []
            && \array_all($expectedFailures, static fn (mixed $entry): bool => \is_string($entry));

        if ($isBareClassList) {
            /** @var list<class-string<\Throwable>> $bareExceptionClasses */
            $bareExceptionClasses = $expectedFailures;
            $expectedFailures = \array_fill_keys($bareExceptionClasses, 0);
        }

        $transports = [];
        foreach ($transportNames as $transportName) {
            $transport = self::getTransport($transportName);

            if (\in_array($transport, $transports, true)) {
                self::fail(\sprintf(
                    'The "%s" transport resolves to the same transport instance as another listed transport'
                    . ' name, which would double-count its messages and failures.',
                    $transportName
                ));
            }

            $transports[$transportName] = $transport;
        }

        $alreadyRejectedCounts = \array_map(
            static fn (InMemoryTransport $transport): int => \count($transport->getRejected()),
            $transports
        );

        $clockAdvances = self::runMessengerWorker($transports);

        $messageFailures = [];
        foreach ($transports as $transportName => $transport) {
            foreach (self::getMessageFailures($transport, $alreadyRejectedCounts[$transportName]) as $failure) {
                $messageFailures[] = $failure;
            }
        }

        if (\array_is_list($expectedFailures) && $expectedFailures !== []) {
            /** @var list<array<class-string<\Throwable>, int|string|null>> $exactExpectedFailures */
            $exactExpectedFailures = $expectedFailures;
            self::assertMessageFailuresExactly($exactExpectedFailures, $messageFailures);
        } else {
            /** @var array<class-string<\Throwable>, int|string|null> $leastOnceExpectedFailures */
            $leastOnceExpectedFailures = $expectedFailures;
            self::assertMessageFailures($leastOnceExpectedFailures, $messageFailures);
        }

        if ($expectedDelays !== null) {
            self::assertClockAdvances($expectedDelays, $clockAdvances);
        }
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

    /**
     * @param array<string, \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport> $transports
     */
    private static function advanceClockToNextDelayedMessage(array $transports, ClockInterface $clock): ?float
    {
        $dueAtTimestamps = [];

        foreach ($transports as $transport) {
            /** @var array<\DateTimeImmutable> $availableAt */
            $availableAt = new ReflectionProperty(InMemoryTransport::class, 'availableAt')->getValue($transport);

            foreach ($availableAt as $dueAt) {
                $dueAtTimestamps[] = (float)$dueAt->format('U.u');
            }
        }

        if ($dueAtTimestamps === []) {
            return null;
        }

        if ($clock instanceof MockClock === false) {
            self::fail(\sprintf(
                'Messages are waiting on a delay, but the clock is not mocked, so waiting the delays out would'
                . ' really sleep. Run the test on a mock clock, e.g. via %s::mockTime().',
                ClockSensitiveTrait::class
            ));
        }

        $nowTimestamp = (float)$clock->now()
            ->format('U.u');
        $advanceBySeconds = (float)\max(0, \min($dueAtTimestamps) - $nowTimestamp);

        $clock->sleep($advanceBySeconds + self::CLOCK_OVERSHOOT_IN_SECONDS);

        if (self::countAvailableMessages($transports) === 0) {
            self::fail(
                'The clock was advanced past the next delay, but no message became available. The messenger'
                . ' transport computes availability from the container "clock" service, while the test advanced'
                . ' the global clock - make both use the same mock clock.'
            );
        }

        return $advanceBySeconds;
    }

    /**
     * @param list<int|float> $expectedDelays
     * @param list<float> $actualAdvances
     */
    private static function assertClockAdvances(array $expectedDelays, array $actualAdvances): void
    {
        $toMilliseconds = static fn (int|float $seconds): int => (int)\round($seconds * 1000);
        $expectedMilliseconds = \array_map($toMilliseconds, $expectedDelays);
        $actualMilliseconds = \array_map($toMilliseconds, $actualAdvances);

        if ($expectedMilliseconds === $actualMilliseconds) {
            return;
        }

        $format = static fn (int $milliseconds): string => \rtrim(\rtrim(\number_format($milliseconds / 1000, 3, '.',
            ''), '0'), '.');

        self::fail(\sprintf(
            'Consuming was expected to move the clock by [%s] second(s), but it moved by [%s].',
            \implode(', ', \array_map($format, $expectedMilliseconds)),
            \implode(', ', \array_map($format, $actualMilliseconds))
        ));
    }

    private static function assertExpectedDelaysShape(?array $expectedDelays): void
    {
        if ($expectedDelays === null) {
            return;
        }

        if (\array_is_list($expectedDelays) === false) {
            self::fail('Expected delays must be a list of seconds, e.g. [10, 1, 30, 60].');
        }

        foreach ($expectedDelays as $delay) {
            if ((\is_int($delay) || \is_float($delay)) === false || \is_finite($delay) === false || $delay < 0) {
                self::fail(\sprintf(
                    'Expected delays must be non-negative numbers of seconds, %s given.',
                    \var_export($delay, true)
                ));
            }
        }
    }

    private static function assertExpectedFailureCodeShape(string $exceptionClass, mixed $expectedCode): void
    {
        if ($expectedCode !== null && \is_int($expectedCode) === false && \is_string($expectedCode) === false) {
            self::fail(\sprintf(
                'An expected exception code must be an integer, a string, or null to accept any code, %s given'
                . ' for %s.',
                \get_debug_type($expectedCode),
                $exceptionClass
            ));
        }
    }

    private static function assertExpectedFailuresShape(array $expectedFailures): void
    {
        if (\array_is_list($expectedFailures)) {
            $hasBareClasses = false;
            $hasPairs = false;

            foreach ($expectedFailures as $index => $expectedFailure) {
                if (\is_string($expectedFailure)) {
                    $hasBareClasses = true;

                    continue;
                }

                if (\is_array($expectedFailure) === false
                    || \count($expectedFailure) !== 1
                    || \is_string(\array_key_first($expectedFailure)) === false) {
                    self::fail(\sprintf(
                        'In a list of expected failures every entry must be an exception class, which is short'
                        . ' for [SomeException::class => 0], or a single-pair map of an exception class to a'
                        . ' code, e.g. [SomeException::class => 42], %s given at index %d.',
                        \is_array($expectedFailure)
                            ? \sprintf('an array of %d entries', \count($expectedFailure))
                            : \get_debug_type($expectedFailure),
                        $index
                    ));
                }

                $exceptionClass = (string)\array_key_first($expectedFailure);
                self::assertExpectedFailureCodeShape($exceptionClass, $expectedFailure[$exceptionClass]);
                $hasPairs = true;
            }

            if ($hasBareClasses && $hasPairs) {
                self::fail(
                    'A list of expected failures must be either all bare exception classes (asserted as thrown'
                    . ' at least once) or all single-pair maps (asserted as the exact list of thrown'
                    . ' exceptions). To keep the exact mode, write a bare class as [SomeException::class => 0].'
                );
            }

            return;
        }

        foreach ($expectedFailures as $exceptionClass => $expectedCode) {
            if (\is_string($exceptionClass) === false) {
                self::fail(\sprintf(
                    'Expected failures must map an exception class to a code (or to null to accept any code),'
                    . ' or be a list of such single-pair maps to assert the exact failures, %s given as a key.'
                    . ' Mixing the two shapes is not supported.',
                    \var_export($exceptionClass, true)
                ));
            }

            self::assertExpectedFailureCodeShape($exceptionClass, $expectedCode);
        }
    }

    /**
     * @param array<class-string<\Throwable>, int|string|null> $expectedFailures
     * @param \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp[] $actualFailures
     */
    private static function assertMessageFailures(array $expectedFailures, array $actualFailures): void
    {
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

    /**
     * @param list<array<class-string<\Throwable>, int|string|null>> $expectedFailures
     * @param \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp[] $actualFailures
     */
    private static function assertMessageFailuresExactly(array $expectedFailures, array $actualFailures): void
    {
        $expectedCodesByClass = [];
        foreach ($expectedFailures as $expectedFailure) {
            foreach ($expectedFailure as $exceptionClass => $expectedCode) {
                $expectedCodesByClass[$exceptionClass][] = $expectedCode;
            }
        }

        $unexpectedFailures = [];
        foreach ($actualFailures as $actualFailure) {
            $exceptionClass = $actualFailure->getExceptionClass();
            $expectedCodes = $expectedCodesByClass[$exceptionClass] ?? [];
            $matchedKey = \array_search($actualFailure->getExceptionCode(), $expectedCodes, true);

            if ($matchedKey === false) {
                $matchedKey = \array_search(null, $expectedCodes, true);
            }

            if ($matchedKey === false) {
                $unexpectedFailures[] = $actualFailure;

                continue;
            }

            unset($expectedCodesByClass[$exceptionClass][$matchedKey]);
        }

        $missingFailures = [];
        foreach ($expectedCodesByClass as $exceptionClass => $expectedCodes) {
            foreach ($expectedCodes as $expectedCode) {
                $missingFailures[] = self::describeExpectedFailure($exceptionClass, $expectedCode);
            }
        }

        if ($missingFailures === [] && $unexpectedFailures === []) {
            return;
        }

        $problems = [];

        if ($missingFailures !== []) {
            $problems[] = "Expected but never thrown:\n" . \implode("\n", $missingFailures);
        }

        if ($unexpectedFailures !== []) {
            $problems[] = "Thrown but not expected:\n\n"
                . \implode("\n\n", \array_map(self::describeFailure(...), $unexpectedFailures));
        }

        self::fail(
            "The thrown exceptions do not match the expected list exactly.\n\n" . \implode("\n\n", $problems)
        );
    }

    /**
     * @param array<string, \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport> $transports
     */
    private static function countAvailableMessages(array $transports): int
    {
        $availableMessagesCount = 0;

        foreach ($transports as $transport) {
            $availableMessagesCount += \iterator_count($transport->get(\PHP_INT_MAX));
        }

        return $availableMessagesCount;
    }

    private static function describeExpectedFailure(string $exceptionClass, int|string|null $expectedCode): string
    {
        return $expectedCode === null
            ? \sprintf(' - %s (any code)', $exceptionClass)
            : \sprintf(' - %s (code: %s)', $exceptionClass, \var_export($expectedCode, true));
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
            $descriptions[] = self::describeExpectedFailure($exceptionClass, $exceptionCode);
        }

        return \implode("\n", $descriptions);
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

    private static function getNextId(InMemoryTransport $transport): int
    {
        /** @var int $nextId */
        $nextId = new ReflectionProperty(InMemoryTransport::class, 'nextId')->getValue($transport);

        return $nextId;
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

    /**
     * @param array<string, \Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport> $transports
     *
     * @return list<float>
     */
    private static function runMessengerWorker(array $transports): array
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

        $expectedSentCounts = \array_map(
            static fn (InMemoryTransport $transport
            ): int => \count($transport->getSent()) - self::getNextId($transport),
            $transports
        );
        $clock = Clock::get();
        $clockAdvances = [];
        $messageLimitListener = null;

        $run = 0;

        try {
            while ($run < self::MAX_WORKER_RUNS) {
                $availableMessagesCount = self::countAvailableMessages($transports);

                if ($availableMessagesCount === 0) {
                    $advancedBySeconds = self::advanceClockToNextDelayedMessage($transports, $clock);

                    if ($advancedBySeconds === null) {
                        break;
                    }

                    $clockAdvances[] = $advancedBySeconds;

                    continue;
                }

                $run++;

                if ($messageLimitListener !== null) {
                    $eventDispatcher->removeSubscriber($messageLimitListener);
                }

                $messageLimitListener = new StopWorkerOnMessageLimitListener($availableMessagesCount);
                $eventDispatcher->addSubscriber($messageLimitListener);

                $worker = new Worker($transports, $messageBus, $eventDispatcher, clock: $clock);
                $worker->run(['fetch_size' => $availableMessagesCount]);
            }
        } finally {
            $eventDispatcher->removeListener(WorkerRunningEvent::class, $stopWorkerOnIdleListener);
            $eventDispatcher->removeSubscriber($resetServicesListener);

            if ($messageLimitListener !== null) {
                $eventDispatcher->removeSubscriber($messageLimitListener);
            }
        }

        foreach ($transports as $transportName => $transport) {
            $expectedSentCount = $expectedSentCounts[$transportName] + self::getNextId($transport);

            if (\count($transport->getSent()) !== $expectedSentCount) {
                self::fail(\sprintf(
                    'The "%s" in-memory transport was reset while consuming messages, so messages were silently'
                    . ' lost. The test transport must survive service resets, e.g. via a transport factory that'
                    . ' keeps its transports across resets.',
                    $transportName
                ));
            }
        }

        $leftoverMessageClasses = [];
        foreach ($transports as $transportName => $transport) {
            foreach (self::getUnhandledEnvelopes($transport) as $envelope) {
                $leftoverMessageClasses[$transportName][] = $envelope->getMessage()::class;
            }
        }

        self::assertSame([], $leftoverMessageClasses, \sprintf(
            'Unable to consume all messages from the "%s" transport within %d worker runs. Either a handler'
            . ' keeps dispatching new messages forever, or the workload needs more waves than the budget allows.',
            \implode('", "', \array_keys($leftoverMessageClasses)),
            self::MAX_WORKER_RUNS
        ));

        return $clockAdvances;
    }
}
