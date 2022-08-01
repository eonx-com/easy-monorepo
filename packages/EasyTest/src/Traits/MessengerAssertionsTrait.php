<?php

declare(strict_types=1);

namespace EonX\EasyTest\Traits;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnTimeLimitListener;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait MessengerAssertionsTrait
{
    /**
     * Asserts that the given count of messages was sent to async transport.
     *
     * @phpstan-param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToAsyncTransport(int $count, ?string $messageClass = null): void
    {
        Assert::assertCount($count, self::getMessagesSentToAsyncTransport($messageClass));
    }

    /**
     * Asserts that the given count of messages was sent to failed transport.
     *
     * @phpstan-param class-string|null $messageClass
     */
    public static function assertCountOfMessagesSentToFailedTransport(int $count, ?string $messageClass = null): void
    {
        Assert::assertCount($count, self::getMessagesSentToFailedTransport($messageClass));
    }

    /**
     * Asserts that the given count of messages was sent to transport.
     *
     * @phpstan-param class-string|null $messageClass
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
     * @param mixed[] $expectedProperties
     *
     * @phpstan-param class-string $messageClass
     */
    public static function assertMessageSentToAsyncTransport(
        string $messageClass,
        ?array $expectedProperties = null,
        ?int $messagesCount = null
    ): void {
        static::assertMessageSentToTransport($messageClass, 'async', $expectedProperties, $messagesCount);
    }

    /**
     * Asserts that the given message class was dispatched by the message bus to failed transport.
     *
     * @param mixed[] $expectedProperties
     *
     * @phpstan-param class-string $messageClass
     */
    public static function assertMessageSentToFailedTransport(
        string $messageClass,
        ?array $expectedProperties = null,
        ?int $messagesCount = null
    ): void {
        static::assertMessageSentToTransport($messageClass, 'failed', $expectedProperties, $messagesCount);
    }

    /**
     * Asserts that the given message class was dispatched by the message bus.
     *
     * @param mixed[] $expectedProperties
     *
     * @phpstan-param class-string $messageClass
     */
    public static function assertMessageSentToTransport(
        string $messageClass,
        string $transportName,
        ?array $expectedProperties = null,
        ?int $messagesCount = null
    ): void {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $envelopes = \array_filter(
            self::getMessagesSentToTransport($transportName),
            static function (object $message) use ($messageClass, $expectedProperties, $propertyAccessor) {
                if ($message instanceof $messageClass === false) {
                    return false;
                }

                foreach ($expectedProperties ?? [] as $property => $expectedValue) {
                    $actualValue = $propertyAccessor->getValue($message, $property);

                    if ($actualValue !== $expectedValue) {
                        return false;
                    }
                }

                return true;
            }
        );

        Assert::assertCount($messagesCount ?? 1, $envelopes);
    }

    public static function consumeAsyncMessages(): void
    {
        self::runMessengerWorker('async', 'default');
    }

    /**
     * Returns dispatched messages by the message bus to async transport.
     *
     * @phpstan-param class-string<TMessageClass>|null $messageClass
     *
     * @phpstan-return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @phpstan-template TMessageClass
     */
    public static function getMessagesSentToAsyncTransport(?string $messageClass = null): array
    {
        return static::getMessagesSentToTransport('async', $messageClass);
    }

    /**
     * Returns dispatched messages by the message bus to failed transport.
     *
     * @phpstan-param class-string<TMessageClass>|null $messageClass
     *
     * @phpstan-return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @phpstan-template TMessageClass
     */
    public static function getMessagesSentToFailedTransport(?string $messageClass = null): array
    {
        return static::getMessagesSentToTransport('failed', $messageClass);
    }

    /**
     * Returns dispatched messages by the message bus to async transport.
     *
     * @phpstan-param class-string<TMessageClass>|null $messageClass
     *
     * @phpstan-return ($messageClass is null ? array<int, object> : array<int, TMessageClass>)
     *
     * @phpstan-template TMessageClass
     */
    public static function getMessagesSentToTransport(string $transportName, ?string $messageClass = null): array
    {
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $transport */
        $transport = KernelTestCase::getContainer()->get("messenger.transport.${transportName}");

        $messages = [];
        foreach ($transport->getSent() as $envelope) {
            $message = $envelope->getMessage();

            if ($messageClass === null || $message instanceof $messageClass) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    private static function runMessengerWorker(string $transportName, string $busName): void
    {
        /** @var \Symfony\Component\Messenger\Transport\InMemoryTransport $asyncTransport */
        $asyncTransport = KernelTestCase::getContainer()->get("messenger.transport.${transportName}");

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = KernelTestCase::getContainer()->get(EventDispatcherInterface::class);

        /** @var \Symfony\Component\Messenger\MessageBusInterface $messageBus */
        $messageBus = KernelTestCase::getContainer()->get("messenger.bus.${busName}");

        $tries = 0;
        $messageLimitListener = null;
        $eventDispatcher->addSubscriber(new StopWorkerOnTimeLimitListener(2));
        /** @var \Symfony\Component\Messenger\EventListener\ResetServicesListener $resetServicesListener */
        $resetServicesListener = KernelTestCase::getContainer()->get('messenger.listener.reset_services');
        $eventDispatcher->addSubscriber($resetServicesListener);
        while (++$tries < 10) {
            $messagesCount = \count((array)$asyncTransport->get());

            if ($messagesCount === 0) {
                break;
            }

            if ($messageLimitListener !== null) {
                $eventDispatcher->removeSubscriber($messageLimitListener);
            }

            $messageLimitListener = new StopWorkerOnMessageLimitListener($messagesCount);
            $eventDispatcher->addSubscriber($messageLimitListener);

            $worker = new Worker(['async' => $asyncTransport], $messageBus, $eventDispatcher);
            $worker->run();
        }
    }
}
