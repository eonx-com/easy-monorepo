<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyLogging\Tests\Stub\Kernel\KernelStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class EasyLoggingMonologBundleTest extends AbstractUnitTestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->bootMonologKernel(
            __DIR__ . '/../../Fixture/config/use_symfony_monolog_bundle.php',
            'test_monolog'
        );
    }

    public function testBugsnagHandlerIsRegisteredForAllChannelsByDefault(): void
    {
        $container = $this->bootMonologKernel(
            __DIR__ . '/../../Fixture/config/use_symfony_monolog_bundle_with_bugsnag_handler_all_channels.php',
            'test_monolog_bugsnag_all_channels'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue($this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class));
        self::assertTrue($this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class));
    }

    public function testBugsnagHandlerIsRegisteredForConfiguredChannelsOnly(): void
    {
        $container = $this->bootMonologKernel(
            __DIR__ . '/../../Fixture/config/use_symfony_monolog_bundle_with_bugsnag_handler.php',
            'test_monolog_bugsnag'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must be attached to the configured "app" channel.'
        );
        self::assertFalse(
            $this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must not be attached to channels outside "bugsnag_handler_channels".'
        );
    }

    public function testEasyLoggingStepsAsideButKeepsFactory(): void
    {
        self::assertTrue((bool)$this->container->getParameter(ConfigParam::UseSymfonyMonologBundle->value));
        self::assertInstanceOf(LoggerInterface::class, $this->container->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerFactoryInterface::class, $this->container->get(LoggerFactoryInterface::class));
    }

    public function testMonologBundleOwnsLoggerService(): void
    {
        $logger = $this->container->get('logger');

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
        $logger = $this->container->get('logger');

        self::assertTrue(
            $this->hasInstanceOf($logger->getProcessors(), SensitiveDataSanitizerProcessor::class),
            'The SensitiveDataSanitizerProcessor must be registered as a monolog.processor.'
        );
    }

    public function testThrowsWhenMonologBundleIsNotRegistered(): void
    {
        $kernel = new KernelStub(
            configs: [__DIR__ . '/../../Fixture/config/use_symfony_monolog_bundle_without_monolog_bundle.php'],
            environment: 'test_monolog_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('use_symfony_monolog_bundle');

        $kernel->boot();
    }

    public function testThrowsWhenSensitiveDataSanitizerIsNotAvailable(): void
    {
        $kernel = new KernelStub(
            configs: [__DIR__ . '/../../Fixture/config/sensitive_data_sanitizer.php'],
            bundles: [new EasyLoggingBundle()],
            environment: 'test_sanitizer_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('eonx-com/easy-utils');

        $kernel->boot();
    }

    private function bootMonologKernel(string $fixture, string $environment): ContainerInterface
    {
        $kernel = new KernelStub(
            configs: [$fixture],
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
    private function hasInstanceOf(iterable $items, string $class): bool
    {
        foreach ($items as $item) {
            if ($item instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
