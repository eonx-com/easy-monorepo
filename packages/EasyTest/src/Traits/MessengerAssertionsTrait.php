<?php

declare(strict_types=1);

namespace EonX\EasyTest\Traits;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\IsEqual;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnTimeLimitListener;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait MessengerAssertionsTrait
{
    /**
     * Asserts that the given count of messages was sent to async transport.
     *
     * @param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToAsyncTransport(int $count, ?string $messageClass = null): void
    {
        Assert::assertCount($count, self::getMessagesSentToAsyncTransport($messageClass));
    }

    /**
     * Asserts that the given count of messages was sent to failed transport.
     *
     * @param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToFailedTransport(int $count, ?string $messageClass = null): void
    {
        Assert::assertCount($count, self::getMessagesSentToFailedTransport($messageClass));
    }

    /**
     * Asserts that the given count of messages was sent to transport.
     *
     * @param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToTransport(
        int $count,
        string $transportName,
        ?string $messageClass = null
    ): void {
        Assert::assertCount($count, self::getMessagesSentToTransport($transportName, $messageClass));
    }

    /**
     * Asserts that the given message class was dispatched by the message bus to async transport.
     *
     * @param class-string $messageClass
     * @param array<string, mixed> $expectedProperties
     */
    public static function assertMessageSentToAsyncTransport(
        string $messageClass,
        array $expectedProperties = [],
        int $messagesCount = 1
    ): void {
        static::assertMessageSentToTransport($messageClass, 'async', $expectedProperties, $messagesCount);
    }

    /**
     * Asserts that the given message class was dispatched by the message bus to failed transport.
     *
     * @param class-string $messageClass
     * @param array<string, mixed> $expectedProperties
     */
    public static function assertMessageSentToFailedTransport(
        string $messageClass,
        array $expectedProperties = [],
        int $messagesCount = 1
    ): void {
        static::assertMessageSentToTransport($messageClass, 'failed', $expectedProperties, $messagesCount);
    }

    /**
     * Asserts that the given message class was dispatched by the message bus.
     *
     * @param class-string $messageClass
     * @param array<string, mixed> $expectedProperties
     */
    public static function assertMessageSentToTransport(
        string $messageClass,
        string $transportName,
        array $expectedProperties = [],
        int $messagesCount = 1
    ): void {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $envelopes = \array_filter(
            self::getMessagesSentToTransport($transportName),
            static function (object $message) use ($messageClass, $expectedProperties, $propertyAccessor) {
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

        Assert::assertCount($messagesCount, $envelopes);
    }

    /**
     * @param array<int, array<class-string<\Throwable>, int|string>> $expectedExceptions
     */
    public static function consumeAsyncMessages(array $expectedExceptions = []): void
    {
        if (isset(self::$isInsideSafeCall) && self::$isInsideSafeCall) {
            throw new RuntimeException(
                "You can't use MessengerAssertionsTrait::consumeAsyncMessages() in ExceptionTrait::safeCall()"
            );
        }

        self::runMessengerWorker('async', 'default', $expectedExceptions);
    }

    /**
     * Returns dispatched messages by the message bus to async transport.
     *
     * @param class-string<TMessageClass>|null $messageClass
     *
     * @return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @template TMessageClass
     */
    public static function getMessagesSentToAsyncTransport(?string $messageClass = null): array
    {
        return static::getMessagesSentToTransport('async', $messageClass);
    }

    /**
     * Returns dispatched messages by the message bus to failed transport.
     *
     * @param class-string<TMessageClass>|null $messageClass
     *
     * @return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @template TMessageClass
     */
    public static function getMessagesSentToFailedTransport(?string $messageClass = null): array
    {
        return static::getMessagesSentToTransport('failed', $messageClass);
    }

    /**
     * Returns dispatched messages by the message bus to async transport.
     *
     * @param class-string<TMessageClass>|null $messageClass
     *
     * @return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @template TMessageClass
     */
    public static function getMessagesSentToTransport(string $transportName, ?string $messageClass = null): array
    {
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $transport */
        $transport = KernelTestCase::getContainer()->get('messenger.transport.' . $transportName);

        $messages = [];
        foreach ($transport->getSent() as $envelope) {
            $message = $envelope->getMessage();

            if ($messageClass === null || $message instanceof $messageClass) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * @param array<int, array<class-string<\Throwable>, int|string>> $expectedExceptions
     */
    private static function runMessengerWorker(
        string $transportName,
        string $busName,
        array $expectedExceptions = []
    ): void {
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $transport */
        $transport = KernelTestCase::getContainer()->get('messenger.transport.' . $transportName);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = KernelTestCase::getContainer()->get(EventDispatcherInterface::class);

        /** @var \Symfony\Component\Messenger\MessageBusInterface $messageBus */
        $messageBus = KernelTestCase::getContainer()->get('messenger.bus.' . $busName);

        $tries = 0;
        $messageLimitListener = null;
        $eventDispatcher->addSubscriber(new StopWorkerOnTimeLimitListener(2));
        /** @var \Symfony\Component\Messenger\EventListener\ResetServicesListener $resetServicesListener */
        $resetServicesListener = KernelTestCase::getContainer()->get('messenger.listener.reset_services');
        $eventDispatcher->addSubscriber($resetServicesListener);
        while (++$tries < 10) {
            $messagesCount = \count((array)$transport->get());

            if ($messagesCount === 0) {
                break;
            }

            if ($messageLimitListener !== null) {
                $eventDispatcher->removeSubscriber($messageLimitListener);
            }

            $messageLimitListener = new StopWorkerOnMessageLimitListener($messagesCount);
            $eventDispatcher->addSubscriber($messageLimitListener);

            $worker = new Worker(['async' => $transport], $messageBus, $eventDispatcher);
            $worker->run();
        }

        if (\count((array)$transport->get()) > 0) {
            throw new \RuntimeException('Unable to consume all messages from async transport.');
        }

        foreach ($transport->getRejected() as $envelope) {
            /** @var \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp $errorDetailsStamp */
            foreach ($envelope->all(ErrorDetailsStamp::class) as $errorDetailsStamp) {
                foreach ($expectedExceptions as $key => $expectedExceptionPair) {
                    if (
                        isset($expectedExceptionPair[$errorDetailsStamp->getExceptionClass()])
                        && (
                            $expectedExceptionPair[$errorDetailsStamp->getExceptionClass()]
                            === $errorDetailsStamp->getExceptionCode()
                        )
                    ) {
                        unset($expectedExceptions[$key]);

                        continue 2;
                    }
                }

                echo "\n";
                echo "\033[31mAn unexpected exception occurred while processing the message.\033[0m\n";
                echo "\n";
                echo 'Exception class: ' . $errorDetailsStamp->getExceptionClass() . "\n";
                echo 'Exception code: ' . $errorDetailsStamp->getExceptionCode() . "\n";
                echo 'Exception message: ' . $errorDetailsStamp->getExceptionMessage() . "\n";
                echo "Stack trace:\n";
                echo $errorDetailsStamp->getFlattenException()
                    ? $errorDetailsStamp->getFlattenException()
                        ->getTraceAsString()
                    : 'No stack trace available.';
                echo "\n";

                throw new RuntimeException('Check the logs above for more details.');
            }
        }

        if (\count($expectedExceptions) > 0) {
            $exceptions = \array_map(
                static fn (array $expectedExceptionPair): string => \key($expectedExceptionPair),
                $expectedExceptions
            );

            throw new RuntimeException(
                "The following exceptions were expected but not thrown: \n - "
                . \implode("\n - ", $exceptions)
            );
        }
    }
}
