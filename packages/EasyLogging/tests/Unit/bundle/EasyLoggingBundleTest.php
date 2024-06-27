<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Logger\LazyLogger;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EasyLoggingBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<array>
     *
     * @see testSymfonyBundle
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
                __DIR__ . '/../../Fixture/config/default_config.php',
            ],
        ];

        yield 'Lazy loggers wildcard' => [
            'assertion' => function (ContainerInterface $container): void {
                $loggerFactory = $container->get(LoggerFactoryInterface::class);

                self::assertInstanceOf(LazyLogger::class, $loggerFactory->create('any'));
            },
            'configs' => [
                __DIR__ . '/../../Fixture/config/lazy_loggers_wildcard.php',
            ],
        ];

        yield 'Lazy logger' => [
            'assertion' => function (ContainerInterface $container): void {
                $loggerFactory = $container->get(LoggerFactoryInterface::class);

                self::assertInstanceOf(LazyLogger::class, $loggerFactory->create('lazy'));
                self::assertNotInstanceOf(LazyLogger::class, $loggerFactory->create('any'));
            },
            'configs' => [
                __DIR__ . '/../../Fixture/config/lazy_logger.php',
            ],
        ];
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('providerTestSymfonyBundle')]
    public function testSymfonyBundle(callable $assertion, ?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        $assertion($container);
    }
}
