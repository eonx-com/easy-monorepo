<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Logger\LazyLogger;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Logger;
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
    public static function provideSymfonyBundleData(): iterable
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

        yield 'Sensitive data sanitizer for the LoggerFactory' => [
            'assertion' => function (ContainerInterface $container): void {
                $logger = $container->get(LoggerFactoryInterface::class)->create('app');

                self::assertInstanceOf(Logger::class, $logger);

                $hasSanitizerProcessor = false;
                foreach ($logger->getProcessors() as $processor) {
                    if ($processor instanceof SensitiveDataSanitizerProcessor) {
                        $hasSanitizerProcessor = true;

                        break;
                    }
                }

                self::assertTrue(
                    $hasSanitizerProcessor,
                    'The SensitiveDataSanitizerProcessor must be registered as a processor config provider.'
                );
            },
            'configs' => [
                __DIR__ . '/../../Fixture/config/sensitive_data_sanitizer.php',
            ],
        ];
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('provideSymfonyBundleData')]
    public function testSymfonyBundle(callable $assertion, ?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        $assertion($container);
    }
}
