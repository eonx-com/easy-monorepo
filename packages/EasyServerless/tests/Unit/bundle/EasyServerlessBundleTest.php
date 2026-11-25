<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use Bref\Bref;
use Bref\Listener\EventDispatcher;
use Bref\Symfony\Messenger\BrefMessengerBundle;
use Closure;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Aws\Warmer\SymfonyHttpHandlerWarmer;
use EonX\EasyServerless\Bundle\Enum\BundleParam;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use EonX\EasyServerless\Bundle\Loader\HealthCheckRouteLoader;
use EonX\EasyServerless\Datadog\Listener\PhpRuntimeInfoListener;
use EonX\EasyServerless\Doctrine\Warmer\ConnectionsWarmer;
use EonX\EasyServerless\Doctrine\Warmer\EntityMetadataWarmer;
use EonX\EasyServerless\Messenger\Listener\ResetServicesListener;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use EonX\EasyServerless\Routing\Warmer\RouteCollectionWarmer;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Warmer\ClassMapWarmer;
use EonX\EasyServerless\Tests\Stub\Kernel\FrameworkKernelStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Psr\Log\NullLogger;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
    private string $markerPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markerPath = \sys_get_temp_dir() . '/' . \uniqid('easy-serverless-snap-start-test-', true);
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->markerPath)) {
            \unlink($this->markerPath);
        }

        parent::tearDown();
    }

    /**
     * @see testBootDoesNotSubscribeSnapStartSubscriberOutsideSnapStartInitialization
     */
    public static function provideInitializationTypeData(): iterable
    {
        yield 'on-demand' => ['on-demand'];
        yield 'provisioned concurrency' => ['provisioned-concurrency'];
    }

    public function testAppMetricClientUsesDedicatedLoggerInsteadOfApplicationLogger(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        $appMetricLogger = $container->get(ConfigServiceId::AppMetricLogger->value);
        $appMetricClient = $container->get(AppMetricClientInterface::class);

        self::assertInstanceOf(Logger::class, $appMetricLogger);
        self::assertSame(AppMetricLoggerFactory::LOGGER_NAME, $appMetricLogger->getName());
        self::assertNotSame($container->get('logger'), $appMetricLogger);
        self::assertSame(
            $appMetricLogger,
            (new ReflectionProperty(AppMetricClient::class, 'logger'))->getValue($appMetricClient)
        );
    }

    public function testAppMetricClientUsesLoggerOverriddenByApplication(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/app_metric_logger_overridden.php']
        )->getContainer();

        $appMetricLogger = $container->get(ConfigServiceId::AppMetricLogger->value);

        self::assertInstanceOf(NullLogger::class, $appMetricLogger);
        self::assertSame(
            $appMetricLogger,
            (new ReflectionProperty(AppMetricClient::class, 'logger'))
                ->getValue($container->get(AppMetricClientInterface::class))
        );
    }

    #[DataProvider('provideInitializationTypeData')]
    #[RunInSeparateProcess]
    public function testBootDoesNotSubscribeSnapStartSubscriberOutsideSnapStartInitialization(
        string $initializationType,
    ): void {
        // The Bref Laravel bridge subscribes its own subscriber when Composer autoloads its files
        Bref::reset();
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=' . $initializationType);
        // The system cache pools must be array pools in a Lambda
        $sut = new FrameworkKernelStub(
            'test',
            true,
            [
                __DIR__ . '/../../Fixture/config/framework_array_cache.php',
                __DIR__ . '/../../Fixture/config/snap_start_enabled.php',
            ]
        );

        $sut->boot();

        self::assertSame([], (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events()));
    }

    #[RunInSeparateProcess]
    public function testBootDoesNotSubscribeSnapStartSubscriberWhenSnapStartDisabled(): void
    {
        // The Bref Laravel bridge subscribes its own subscriber when Composer autoloads its files
        Bref::reset();
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=snap-start');
        // The system cache pools must be array pools in a Lambda
        $sut = new FrameworkKernelStub(
            'test',
            true,
            [__DIR__ . '/../../Fixture/config/framework_array_cache.php']
        );

        $sut->boot();

        self::assertSame([], (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events()));
    }

    #[RunInSeparateProcess]
    public function testBootSubscribesSnapStartSubscriberInSnapStartLambda(): void
    {
        // The Bref Laravel bridge subscribes its own subscriber when Composer autoloads its files
        Bref::reset();
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=snap-start');
        // Unlike KernelStub, FrameworkKernelStub does not make all the services public, so it catches a private
        // subscriber. The system cache pools must be array pools in a Lambda
        $sut = new FrameworkKernelStub(
            'test',
            true,
            [
                __DIR__ . '/../../Fixture/config/framework_array_cache.php',
                __DIR__ . '/../../Fixture/config/snap_start_enabled.php',
            ]
        );

        $sut->boot();

        self::assertSame(
            [$sut->getContainer()->get(SnapStartLifecycleSubscriber::class)],
            (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events())
        );
    }

    public function testDisableDatadogSucceeds(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/datadog_disabled.php']
        )->getContainer();

        self::assertFalse($container->has(PhpRuntimeInfoListener::class));
    }

    public function testDisableMonologSucceeds(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/monolog_disabled.php']
        )->getContainer();

        self::assertFalse($container->has(PhpSourceProcessor::class));
    }

    public function testDisableResetServicesSucceeds(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/sqs_reset_services_disabled.php'],
            bundles: [new BrefMessengerBundle()]
        )->getContainer();

        self::assertFalse($container->has(ResetServicesListener::class));
    }

    public function testResetServicesEnabledByDefault(): void
    {
        $container = $this->getKernel(
            bundles: [new BrefMessengerBundle()]
        )->getContainer();

        self::assertTrue($container->has(ResetServicesListener::class));
    }

    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertTrue($container->has(PhpSourceProcessor::class));
        self::assertTrue($container->has(PhpRuntimeInfoListener::class));
        self::assertTrue($container->hasParameter(ConfigParam::AssetsSeparateDomainEnabled->value));
    }

    public function testSnapStartCollectsApplicationWarmersAndAfterRestoreHooks(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/snap_start_application_extensions.php']
        )->getContainer();
        // Closures that create the services, keyed by service id, in priority order. The application warmer tagged by
        // autoconfiguration has the priority 0, the other one -1000
        $expectedWarmers = [
            ClassMapWarmer::class => $container->get(ClassMapWarmer::class),
            RouteCollectionWarmer::class => $container->get(RouteCollectionWarmer::class),
            SnapStartWarmerStub::class => $container->get(SnapStartWarmerStub::class),
            SymfonyHttpHandlerWarmer::class => $container->get(SymfonyHttpHandlerWarmer::class),
            'app.low_priority_snap_start_warmer' => $container->get('app.low_priority_snap_start_warmer'),
        ];
        $expectedAfterRestoreHooks = [
            AfterRestoreHookStub::class => $container->get(AfterRestoreHookStub::class),
        ];

        $snapStartLifecycleSubscriber = $container->get(SnapStartLifecycleSubscriber::class);

        self::assertInstanceOf(SnapStartLifecycleSubscriber::class, $snapStartLifecycleSubscriber);
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedWarmers,
            \array_map(static fn (Closure $warmer): object => $warmer(), \iterator_to_array($warmers))
        );
        /** @var iterable<string, \Closure(): object> $afterRestoreHooks */
        $afterRestoreHooks = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'afterRestoreHooks'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedAfterRestoreHooks,
            \array_map(
                static fn (Closure $afterRestoreHook): object => $afterRestoreHook(),
                \iterator_to_array($afterRestoreHooks)
            )
        );
    }

    public function testSnapStartDefaultWarmersCanBeDisabled(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/snap_start_warmers_disabled.php']
        )->getContainer();
        $expectedWarmers = [
            RouteCollectionWarmer::class => $container->get(RouteCollectionWarmer::class),
        ];

        $snapStartLifecycleSubscriber = $container->get(SnapStartLifecycleSubscriber::class);

        self::assertInstanceOf(SnapStartLifecycleSubscriber::class, $snapStartLifecycleSubscriber);
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedWarmers,
            \array_map(static fn (Closure $warmer): object => $warmer(), \iterator_to_array($warmers))
        );
        self::assertFalse($container->has(ClassMapWarmer::class));
        self::assertFalse($container->has(SymfonyHttpHandlerWarmer::class));
    }

    public function testSnapStartDisabledByDefault(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertFalse($container->has(SnapStartLifecycleSubscriber::class));
        self::assertFalse($container->has(ClassMapWarmer::class));
        self::assertFalse($container->has(RouteCollectionWarmer::class));
        self::assertFalse($container->has(SymfonyHttpHandlerWarmer::class));
    }

    public function testSnapStartPassesConfigToDefaultWarmers(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/snap_start_warmers_configured.php']
        )->getContainer();
        $expectedNamespaces = ['App\\Api\\', 'App\\Domain\\'];
        $expectedHost = 'api.example.com';
        $expectedPath = '/ping';

        $classMapWarmer = $container->get(ClassMapWarmer::class);
        $symfonyHttpHandlerWarmer = $container->get(SymfonyHttpHandlerWarmer::class);

        self::assertSame(
            $expectedNamespaces,
            (new ReflectionProperty(ClassMapWarmer::class, 'namespaces'))->getValue($classMapWarmer)
        );
        self::assertSame(
            $expectedHost,
            (new ReflectionProperty(SymfonyHttpHandlerWarmer::class, 'host'))->getValue($symfonyHttpHandlerWarmer)
        );
        self::assertSame(
            $expectedPath,
            (new ReflectionProperty(SymfonyHttpHandlerWarmer::class, 'path'))->getValue($symfonyHttpHandlerWarmer)
        );
    }

    public function testSnapStartPassesDefaultConfigToDefaultWarmers(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/snap_start_enabled.php']
        )->getContainer();
        $expectedNamespaces = ['App\\'];
        $expectedPath = '/easy-serverless/health-check';
        // The default warm-up request must reach the health check route of the bundle
        $healthCheckRoutePath = (new HealthCheckRouteLoader())
            ->load('.', BundleParam::RouteType->value)
            ->get('easy_serverless.health_check')
            ?->getPath();

        $classMapWarmer = $container->get(ClassMapWarmer::class);
        $symfonyHttpHandlerWarmer = $container->get(SymfonyHttpHandlerWarmer::class);

        self::assertSame(
            $expectedNamespaces,
            (new ReflectionProperty(ClassMapWarmer::class, 'namespaces'))->getValue($classMapWarmer)
        );
        self::assertNull(
            (new ReflectionProperty(SymfonyHttpHandlerWarmer::class, 'host'))->getValue($symfonyHttpHandlerWarmer)
        );
        self::assertSame(
            $expectedPath,
            (new ReflectionProperty(SymfonyHttpHandlerWarmer::class, 'path'))->getValue($symfonyHttpHandlerWarmer)
        );
        self::assertSame($expectedPath, $healthCheckRoutePath);
    }

    public function testSnapStartRegistersDoctrineWarmersWithConnectionsWarmerLast(): void
    {
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [
                __DIR__ . '/../../Fixture/config/doctrine_sqlite.php',
                __DIR__ . '/../../Fixture/config/snap_start_application_extensions.php',
            ],
            [new DoctrineBundle()]
        );
        $kernel->boot();
        $container = $kernel->getContainer();
        // The classes of the services created by the closures, keyed by service id. The ConnectionsWarmer runs after
        // the application warmer with the priority -1000. The services are private, the container cannot return them
        $expectedWarmerClasses = [
            ClassMapWarmer::class => ClassMapWarmer::class,
            EntityMetadataWarmer::class => EntityMetadataWarmer::class,
            RouteCollectionWarmer::class => RouteCollectionWarmer::class,
            SnapStartWarmerStub::class => SnapStartWarmerStub::class,
            SymfonyHttpHandlerWarmer::class => SymfonyHttpHandlerWarmer::class,
            'app.low_priority_snap_start_warmer' => SnapStartWarmerStub::class,
            ConnectionsWarmer::class => ConnectionsWarmer::class,
        ];

        $snapStartLifecycleSubscriber = $container->get(SnapStartLifecycleSubscriber::class);

        self::assertInstanceOf(SnapStartLifecycleSubscriber::class, $snapStartLifecycleSubscriber);
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedWarmerClasses,
            \array_map(static fn (Closure $warmer): string => $warmer()::class, \iterator_to_array($warmers))
        );
    }

    public function testSnapStartRegistersSubscriberWithDefaultWarmersInPriorityOrder(): void
    {
        $container = $this->getKernel(
            configs: [__DIR__ . '/../../Fixture/config/snap_start_enabled.php']
        )->getContainer();
        // Closures that create the services, keyed by service id, in priority order
        $expectedWarmers = [
            ClassMapWarmer::class => $container->get(ClassMapWarmer::class),
            RouteCollectionWarmer::class => $container->get(RouteCollectionWarmer::class),
            SymfonyHttpHandlerWarmer::class => $container->get(SymfonyHttpHandlerWarmer::class),
        ];

        $snapStartLifecycleSubscriber = $container->get(SnapStartLifecycleSubscriber::class);

        self::assertInstanceOf(SnapStartLifecycleSubscriber::class, $snapStartLifecycleSubscriber);
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedWarmers,
            \array_map(static fn (Closure $warmer): object => $warmer(), \iterator_to_array($warmers))
        );
        /** @var iterable<string, \Closure(): object> $afterRestoreHooks */
        $afterRestoreHooks = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'afterRestoreHooks'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame([], \iterator_to_array($afterRestoreHooks));
    }

    public function testSnapStartRunsNextWarmersWhenWarmerCannotBeCreated(): void
    {
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [
                __DIR__ . '/../../Fixture/config/doctrine_sqlite.php',
                __DIR__ . '/../../Fixture/config/snap_start_warmer_not_creatable.php',
            ],
            [new DoctrineBundle()]
        );
        $kernel->boot();
        // The subscriber of the container would write its marker to the default path, so the test gives its warmers
        // to a subscriber with a test marker path
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($kernel->getContainer()->get(SnapStartLifecycleSubscriber::class));
        $logHandler = new TestHandler();
        $sut = new SnapStartLifecycleSubscriber(
            warmers: $warmers,
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );
        $expectedNotCreatableWarmer = 'app.not_creatable_snap_start_warmer';
        $expectedWarmerClasses = [
            EntityMetadataWarmer::class,
            RouteCollectionWarmer::class,
            ConnectionsWarmer::class,
        ];

        $sut->afterStartup();

        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmer could not be created', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedNotCreatableWarmer, $logHandler->getRecords()[0]->context['warmer']);
        self::assertInstanceOf(EnvNotFoundException::class, $logHandler->getRecords()[0]->context['exception']);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        /** @var array<class-string, int> $warmerDurations */
        $warmerDurations = $logHandler->getRecords()[1]
            ->context['warmers'];
        self::assertSame($expectedWarmerClasses, \array_keys($warmerDurations));
        self::assertFileExists($this->markerPath);
    }
}
