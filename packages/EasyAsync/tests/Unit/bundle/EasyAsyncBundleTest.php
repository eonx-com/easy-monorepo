<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Bundle;

use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnMessagesLimitSubscriber;
use EonX\EasyAsync\Messenger\Subscriber\StopWorkerOnTimeLimitSubscriber;
use EonX\EasyAsync\Tests\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EasyAsyncBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testMessengerConfig
     */
    public static function providerTestMessengerConfig(): iterable
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
                __DIR__ . '/../../Fixture/config/messages_limit_only.yaml',
            ],
        ];

        yield 'time only' => [
            static function (ContainerInterface $container): void {
                self::assertFalse($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertTrue($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
            [
                __DIR__ . '/../../Fixture/config/time_limit_only.yaml',
            ],
        ];

        yield 'both limits' => [
            static function (ContainerInterface $container): void {
                self::assertTrue($container->has(StopWorkerOnMessagesLimitSubscriber::class));
                self::assertTrue($container->has(StopWorkerOnTimeLimitSubscriber::class));
            },
            [
                __DIR__ . '/../../Fixture/config/both_limits.yaml',
            ],
        ];
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('providerTestMessengerConfig')]
    public function testMessengerConfig(callable $assert, ?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        \call_user_func($assert, $container);
    }
}
