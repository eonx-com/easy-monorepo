<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony;

use EonX\EasyAsync\Bridge\Symfony\Messenger\StopWorkerOnMessagesLimitSubscriber;
use EonX\EasyAsync\Bridge\Symfony\Messenger\StopWorkerOnTimeLimitSubscriber;
use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EasyAsyncBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestMessengerConfig(): iterable
    {
        yield 'no config - no subscribers' => [
            static function (ContainerInterface $container): void {
                self::assertFalse($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertFalse($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
        ];

        yield 'messages only' => [
            static function (ContainerInterface $container): void {
                self::assertTrue($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertFalse($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
            [
                __DIR__ . '/Fixtures/config/messages_limit_only.yaml',
            ]
        ];

        yield 'time only' => [
            static function (ContainerInterface $container): void {
                self::assertFalse($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertTrue($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
            [
                __DIR__ . '/Fixtures/config/time_limit_only.yaml',
            ]
        ];

        yield 'both limits' => [
            static function (ContainerInterface $container): void {
                self::assertTrue($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertTrue($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
            [
                __DIR__ . '/Fixtures/config/both_limits.yaml',
            ]
        ];
    }

    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertInstanceOf(BatchFactoryInterface::class, $container->get(BatchFactoryInterface::class));
    }

    /**
     * @dataProvider providerTestMessengerConfig
     *
     * @param null|string[] $configs
     */
    public function testMessengerConfig(callable $assert, ?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        \call_user_func($assert, $container);
    }
}
