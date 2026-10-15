<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use DateTimeImmutable;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyLogging\Tests\Stub\Kernel\KernelStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class EasyLoggingMonologBundleTest extends AbstractUnitTestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../Fixture/config/';

    private ?ContainerInterface $container = null;

    public function testBugsnagHandlerChannelsAreMergedAcrossConfigs(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_channels.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_more_channels.php',
            ],
            'test_monolog_bugsnag_more_channels'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue(
            $this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The channels of the first config file must be kept when another config file adds channels.'
        );
        self::assertTrue(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The channels of the following config files must be merged with the ones of the first config file.'
        );
    }

    public function testBugsnagHandlerFormatterCanBeOverriddenByApplication(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_formatter.php',
            ],
            'test_monolog_bugsnag_formatter'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        $bugsnagHandler = null;

        foreach ($appLogger->getHandlers() as $handler) {
            if ($handler instanceof BugsnagMonologHandler) {
                $bugsnagHandler = $handler;

                break;
            }
        }

        self::assertInstanceOf(BugsnagMonologHandler::class, $bugsnagHandler);
        self::assertInstanceOf(LineFormatter::class, $bugsnagHandler->getFormatter());
        self::assertSame(
            'Some message',
            $bugsnagHandler->getFormatter()
                ->format(new LogRecord(new DateTimeImmutable(), 'app', Level::Error, 'Some message')),
            'The application definition of the handler (with its formatter) must win over the bundle one.'
        );
    }

    public function testBugsnagHandlerIsNotRegisteredWhenDeclaredByApplication(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_declared_by_app.php',
            ],
            'test_monolog_bugsnag_declared_by_app'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertFalse(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The bundle must not attach the handler when the application declares it itself.'
        );
        self::assertSame(
            1,
            $this->countInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The handler declared by the application must be attached exactly once.'
        );
    }

    public function testBugsnagHandlerIsNotRegisteredWhenDeclaredByApplicationUnderCustomServiceId(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_declared_by_app_custom_id.php',
            ],
            'test_monolog_bugsnag_declared_by_app_custom_id'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertFalse(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The bundle must not attach the handler when the application declares it under its own service id.'
        );
        self::assertSame(
            1,
            $this->countInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The handler declared by the application must be attached exactly once.'
        );
    }

    public function testBugsnagHandlerIsRegisteredForConfiguredChannelsOnly(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_channels.php',
            ],
            'test_monolog_bugsnag_channels'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue(
            $this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must be attached to the configured "other" channel.'
        );
        self::assertFalse(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must not be attached to channels outside "bugsnag_handler_channels".'
        );
    }

    public function testBugsnagHandlerIsRegisteredForMainChannelByDefault(): void
    {
        $container = $this->bootMonologKernel(
            [self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php'],
            'test_monolog_bugsnag'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must be attached to the main "app" channel when "bugsnag_handler_channels" is empty.'
        );
        self::assertFalse(
            $this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must not be attached to other channels when "bugsnag_handler_channels" is empty.'
        );
    }

    public function testBugsnagHandlerIsRegisteredForMainChannelWhenDefaultChannelIsCustom(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_custom_default_channel.php',
            ],
            'test_monolog_bugsnag_custom_default_channel'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');

        self::assertTrue(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'A custom "default_channel" (a LoggerFactory option) must not move the handler away from the "app" channel.'
        );
    }

    public function testBugsnagHandlerUsesConfiguredLevel(): void
    {
        $container = $this->bootMonologKernel(
            [
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler.php',
                self::FIXTURE_DIR . 'use_symfony_monolog_bundle_with_bugsnag_handler_level.php',
            ],
            'test_monolog_bugsnag_level'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        $bugsnagHandler = null;

        foreach ($appLogger->getHandlers() as $handler) {
            if ($handler instanceof BugsnagMonologHandler) {
                $bugsnagHandler = $handler;

                break;
            }
        }

        self::assertInstanceOf(BugsnagMonologHandler::class, $bugsnagHandler);
        self::assertSame(
            Level::Error,
            $bugsnagHandler->getLevel(),
            'The "bugsnag_handler_level" option must apply to the handler attached through symfony/monolog-bundle.'
        );
    }

    public function testEasyLoggingStepsAsideButKeepsFactory(): void
    {
        $container = $this->getContainer();

        self::assertTrue((bool)$container->getParameter(ConfigParam::UseSymfonyMonologBundle->value));
        self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerFactoryInterface::class, $container->get(LoggerFactoryInterface::class));
    }

    public function testMonologBundleOwnsLoggerService(): void
    {
        $logger = $this->getContainer()
            ->get('logger');

        // The channel-replacement pass steps aside, so "logger" is the symfony/monolog-bundle logger, recognisable by
        // the TestHandler declared in the fixture (the EasyLogging factory never uses it)
        self::assertInstanceOf(Logger::class, $logger);
        self::assertTrue(
            $this->hasInstanceOf($logger->getHandlers(), TestHandler::class),
            'The "logger" service must be provided by symfony/monolog-bundle.'
        );
    }

    public function testSensitiveDataSanitizerIsRegisteredAsMonologProcessor(): void
    {
        $logger = $this->getContainer()
            ->get('logger');

        self::assertTrue(
            $this->hasInstanceOf($logger->getProcessors(), SensitiveDataSanitizerProcessor::class),
            'The SensitiveDataSanitizerProcessor must be registered as a monolog.processor.'
        );
    }

    public function testThrowsWhenMonologBundleIsNotRegistered(): void
    {
        $kernel = new KernelStub(
            configs: [self::FIXTURE_DIR . 'use_symfony_monolog_bundle_without_monolog_bundle.php'],
            environment: 'test_monolog_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('use_symfony_monolog_bundle');

        $kernel->boot();
    }

    public function testThrowsWhenSensitiveDataSanitizerIsNotAvailable(): void
    {
        $kernel = new KernelStub(
            configs: [self::FIXTURE_DIR . 'sensitive_data_sanitizer.php'],
            bundles: [new EasyLoggingBundle()],
            environment: 'test_sanitizer_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('eonx-com/easy-utils');

        $kernel->boot();
    }

    /**
     * @param string[] $configs
     */
    private function bootMonologKernel(array $configs, string $environment): ContainerInterface
    {
        $kernel = new KernelStub(
            configs: $configs,
            bundles: [new MonologBundle(), new EasyUtilsBundle(), new EasyLoggingBundle()],
            environment: $environment
        );
        $kernel->boot();

        return $kernel->getContainer();
    }

    /**
     * @param iterable<object|callable> $items
     * @param class-string $class
     */
    private function countInstanceOf(iterable $items, string $class): int
    {
        $count = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $count++;
            }
        }

        return $count;
    }

    private function getContainer(): ContainerInterface
    {
        return $this->container ??= $this->bootMonologKernel(
            [self::FIXTURE_DIR . 'use_symfony_monolog_bundle.php'],
            'test_monolog'
        );
    }

    /**
     * @param iterable<object|callable> $items
     * @param class-string $class
     */
    private function hasInstanceOf(iterable $items, string $class): bool
    {
        return $this->countInstanceOf($items, $class) > 0;
    }
}
