<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use DateTimeImmutable;
use EonX\EasyLogging\Bundle\EasyLoggingBundle;
use EonX\EasyLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Formatter\JsonFormatter;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use EonX\EasyLogging\Tests\Stub\Kernel\KernelStub;
use EonX\EasyLogging\Tests\Stub\Processor\SelfConfigProvidingProcessorStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\Bundle\EasyUtilsBundle;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class EasyLoggingBundleTest extends AbstractUnitTestCase
{
    private const string FIXTURE_DIR = __DIR__ . '/../../Fixture/config/';

    /**
     * prependExtension() reads the raw "bugsnag_handler" node before the config tree processes it: it must decide like
     * canBeEnabled() does, otherwise the handler is either attached while its service is missing or silently attached
     * although disabled.
     *
     * @see testBugsnagHandlerRawConfigMatchesProcessedConfig
     */
    public static function provideBugsnagHandlerRawConfigMatchesProcessedConfigData(): iterable
    {
        yield 'Not configured' => [
            'fixtures' => [],
            'expectedChannel' => null,
        ];
        yield 'Boolean shorthand false' => [
            'fixtures' => ['bugsnag_handler_disabled_shorthand.php'],
            'expectedChannel' => null,
        ];
        yield 'Boolean shorthand true' => [
            'fixtures' => ['bugsnag_handler.php'],
            'expectedChannel' => 'app',
        ];
        yield 'Enabled false' => [
            'fixtures' => ['bugsnag_handler_disabled.php'],
            'expectedChannel' => null,
        ];
        yield 'Enabled true' => [
            'fixtures' => ['bugsnag_handler_enabled.php'],
            'expectedChannel' => 'app',
        ];
        yield 'Level only enables the handler' => [
            'fixtures' => ['bugsnag_handler_level.php'],
            'expectedChannel' => 'app',
        ];
        yield 'Channels only enable the handler' => [
            'fixtures' => ['bugsnag_handler_channels.php'],
            'expectedChannel' => 'other',
        ];
        yield 'Enabled false in a later config wins' => [
            'fixtures' => ['bugsnag_handler.php', 'bugsnag_handler_disabled.php'],
            'expectedChannel' => null,
        ];
        yield 'Later config without "enabled" re-enables the handler' => [
            'fixtures' => ['bugsnag_handler_disabled.php', 'bugsnag_handler_channels.php'],
            'expectedChannel' => 'other',
        ];
    }

    /**
     * @see testThrowsOnLegacyLoggerFactoryService
     */
    public static function provideThrowsOnLegacyLoggerFactoryServiceData(): iterable
    {
        yield 'Handler config provider' => [
            'fixture' => 'legacy_handler_config_provider.php',
            'interface' => HandlerConfigProviderInterface::class,
        ];
        yield 'Processor config provider neither autoconfigured nor tagged monolog.processor' => [
            'fixture' => 'legacy_processor_config_provider.php',
            'interface' => ProcessorConfigProviderInterface::class,
        ];
    }

    public function testBugsnagHandlerChannelsAreMergedAcrossConfigs(): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_channels.php',
                self::FIXTURE_DIR . 'bugsnag_handler_more_channels.php',
            ],
            'test_bugsnag_more_channels'
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
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_formatter.php',
            ],
            'test_bugsnag_formatter'
        );

        $bugsnagHandler = $this->findBugsnagHandler($container);

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
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_declared_by_app.php',
            ],
            'test_bugsnag_declared_by_app'
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
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_declared_by_app_custom_id.php',
            ],
            'test_bugsnag_declared_by_app_custom_id'
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

    public function testBugsnagHandlerIsNotRegisteredWhenDeclaredByApplicationUnderParameterServiceId(): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_declared_by_app_parameter_id.php',
            ],
            'test_bugsnag_declared_by_app_parameter_id'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertFalse(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The bundle must resolve container parameters in the service id declared by the application.'
        );
        self::assertSame(
            1,
            $this->countInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The handler declared by the application must be attached exactly once.'
        );
    }

    public function testBugsnagHandlerIsRegisteredForConfiguredChannelsOnly(): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
                self::FIXTURE_DIR . 'bugsnag_handler_channels.php',
            ],
            'test_bugsnag_channels'
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
            'The Bugsnag handler must not be attached to channels outside "bugsnag_handler.channels".'
        );
    }

    public function testBugsnagHandlerIsRegisteredForMainChannelByDefault(): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler.php',
            ],
            'test_bugsnag'
        );

        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');
        /** @var \Monolog\Logger $otherLogger */
        $otherLogger = $container->get('monolog.logger.other');

        self::assertTrue(
            $this->hasInstanceOf($appLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must be attached to the main "app" channel when "bugsnag_handler.channels" is empty.'
        );
        self::assertFalse(
            $this->hasInstanceOf($otherLogger->getHandlers(), BugsnagMonologHandler::class),
            'The Bugsnag handler must not be attached to other channels when "bugsnag_handler.channels" is empty.'
        );
    }

    /**
     * @param string[] $fixtures
     */
    #[DataProvider('provideBugsnagHandlerRawConfigMatchesProcessedConfigData')]
    public function testBugsnagHandlerRawConfigMatchesProcessedConfig(array $fixtures, ?string $expectedChannel): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                ...\array_map(static fn(string $fixture): string => self::FIXTURE_DIR . $fixture, $fixtures),
            ],
            'test_bugsnag_matrix_' . \md5(\implode(',', $fixtures))
        );

        self::assertSame($expectedChannel !== null, $container->has(BugsnagMonologHandler::class));

        foreach (['app', 'other'] as $channel) {
            /** @var \Monolog\Logger $logger */
            $logger = $container->get($channel === 'app' ? 'logger' : 'monolog.logger.' . $channel);

            self::assertSame(
                $channel === $expectedChannel,
                $this->hasInstanceOf($logger->getHandlers(), BugsnagMonologHandler::class),
                \sprintf('Unexpected Bugsnag handler presence on the "%s" channel.', $channel)
            );
        }
    }

    public function testBugsnagHandlerUsesConfiguredLevel(): void
    {
        $container = $this->bootKernel(
            [
                self::FIXTURE_DIR . 'bugsnag_client.php',
                self::FIXTURE_DIR . 'bugsnag_handler_level.php',
            ],
            'test_bugsnag_level'
        );

        self::assertSame(
            Level::Error,
            $this->findBugsnagHandler($container)
                ->getLevel(),
            'The "bugsnag_handler.level" option must apply to the handler attached through symfony/monolog-bundle.'
        );
    }

    public function testDefaultConfigLeavesLoggerToMonologBundle(): void
    {
        $container = $this->bootKernel([], 'test_default');

        self::assertInstanceOf(Logger::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(JsonFormatter::class, $container->get(ConfigServiceId::JsonFormatter->value));
        self::assertFalse(
            $container->has(LoggerFactoryInterface::class),
            'The LoggerFactory is a Laravel-only mechanism and must not be registered in Symfony.'
        );
        self::assertFalse($container->has(BugsnagMonologHandler::class));
    }

    public function testLegacyProcessorConfigProviderIsAllowedWhenAutoconfigured(): void
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->bootKernel(
            [self::FIXTURE_DIR . 'legacy_processor_config_provider_autoconfigured.php'],
            'test_legacy_processor_autoconfigured'
        )->get('logger');

        self::assertTrue($this->hasInstanceOf($logger->getProcessors(), SelfConfigProvidingProcessorStub::class));
    }

    public function testMonologBundleOwnsLoggerService(): void
    {
        $logger = $this->bootKernel([self::FIXTURE_DIR . 'monolog_test_handler.php'], 'test_monolog_test_handler')
            ->get('logger');

        // Recognisable by the TestHandler declared in the fixture
        self::assertInstanceOf(Logger::class, $logger);
        self::assertTrue(
            $this->hasInstanceOf($logger->getHandlers(), TestHandler::class),
            'The "logger" service must be provided by symfony/monolog-bundle.'
        );
    }

    public function testSensitiveDataSanitizerCanBeDisabledWithoutEasyUtils(): void
    {
        $kernel = new KernelStub(
            configs: [self::FIXTURE_DIR . 'sensitive_data_sanitizer_disabled.php'],
            bundles: [new MonologBundle(), new EasyLoggingBundle()],
            environment: 'test_sanitizer_disabled'
        );
        $kernel->boot();

        self::assertFalse($kernel->getContainer()->has(SensitiveDataSanitizerProcessor::class));
    }

    public function testSensitiveDataSanitizerIsEnabledByDefault(): void
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->bootKernel([], 'test_default')
            ->get('logger');

        self::assertTrue(
            $this->hasInstanceOf($logger->getProcessors(), SensitiveDataSanitizerProcessor::class),
            'The SensitiveDataSanitizerProcessor must be enabled by default.'
        );
    }

    #[DataProvider('provideThrowsOnLegacyLoggerFactoryServiceData')]
    public function testThrowsOnLegacyLoggerFactoryService(string $fixture, string $interface): void
    {
        $kernel = new KernelStub(
            configs: [self::FIXTURE_DIR . $fixture],
            environment: 'test_legacy_' . \md5($fixture)
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($interface);

        $kernel->boot();
    }

    public function testThrowsWhenMonologBundleIsNotRegistered(): void
    {
        $kernel = new KernelStub(
            configs: [self::FIXTURE_DIR . 'without_monolog_bundle.php'],
            bundles: [new EasyUtilsBundle(), new EasyLoggingBundle()],
            environment: 'test_monolog_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('symfony/monolog-bundle');

        $kernel->boot();
    }

    public function testThrowsWhenSensitiveDataSanitizerIsNotAvailable(): void
    {
        // Enabled by default: no config needed to require EasyUtils
        $kernel = new KernelStub(
            bundles: [new MonologBundle(), new EasyLoggingBundle()],
            environment: 'test_sanitizer_missing'
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('eonx-com/easy-utils');

        $kernel->boot();
    }

    /**
     * @param string[] $configs
     */
    private function bootKernel(array $configs, string $environment): ContainerInterface
    {
        $kernel = new KernelStub(configs: $configs, environment: $environment);
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

    private function findBugsnagHandler(ContainerInterface $container): BugsnagMonologHandler
    {
        /** @var \Monolog\Logger $appLogger */
        $appLogger = $container->get('logger');

        foreach ($appLogger->getHandlers() as $handler) {
            if ($handler instanceof BugsnagMonologHandler) {
                return $handler;
            }
        }

        self::fail('The Bugsnag handler is not attached to the "app" channel.');
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
