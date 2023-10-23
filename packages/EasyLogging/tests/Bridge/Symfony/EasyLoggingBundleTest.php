<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasyLogging\LazyLoggerProxy;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EasyLoggingBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function providerTestSymfonyBundle(): iterable
    {
        yield 'Sanity test with default config' => [
            'assertion' => function (ContainerInterface $container): void {
                self::assertInstanceOf(LoggerFactoryInterface::class, $container->get(LoggerFactoryInterface::class));
                self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));
                self::assertInstanceOf(LoggerInterface::class, $container->get('logger'));
                self::assertInstanceOf(LoggerInterface::class, $container->get('easy_logging.logger'));
            },
            'configs' => [
                __DIR__ . '/Fixtures/default_config.yaml',
            ],
        ];

        yield 'Lazy loggers wildcard' => [
            'assertion' => function (ContainerInterface $container): void {
                $loggerFactory = $container->get(LoggerFactoryInterface::class);

                self::assertInstanceOf(LazyLoggerProxy::class, $loggerFactory->create('any'));
            },
            'configs' => [
                __DIR__ . '/Fixtures/lazy_loggers_wildcard.yaml',
            ],
        ];

        yield 'Lazy logger' => [
            'assertion' => function (ContainerInterface $container): void {
                $loggerFactory = $container->get(LoggerFactoryInterface::class);

                self::assertInstanceOf(LazyLoggerProxy::class, $loggerFactory->create('lazy'));
                self::assertNotInstanceOf(LazyLoggerProxy::class, $loggerFactory->create('any'));
            },
            'configs' => [
                __DIR__ . '/Fixtures/lazy_logger.yaml',
            ],
        ];
    }

    /**
     * @param string[]|null $configs
     *
     * @dataProvider providerTestSymfonyBundle
     */
    public function testSymfonyBundle(callable $assertion, ?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        $assertion($container);
    }
}
