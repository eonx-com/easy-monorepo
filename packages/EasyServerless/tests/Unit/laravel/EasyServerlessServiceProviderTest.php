<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use Bref\Bref;
use Bref\Listener\BrefEventSubscriber;
use Bref\Listener\EventDispatcher;
use Closure;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use EonX\EasyServerless\Laravel\EasyServerlessServiceProvider;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Laravel\Subscribers\LazySnapStartLifecycleSubscriber;
use EonX\EasyServerless\Laravel\Warmers\DatabaseConnectionsWarmer;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Warmer\ClassMapWarmer;
use EonX\EasyServerless\Tests\Stub\Middleware\ShortCircuitMiddlewareStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use Illuminate\Config\Repository;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Lumen\Application as LumenApplication;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

use function DDTrace\root_span;

final class EasyServerlessServiceProviderTest extends AbstractLumenTestCase
{
    private string $markerPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markerPath = \sys_get_temp_dir() . '/easy-serverless-snap-start-test-' . \bin2hex(\random_bytes(8));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->markerPath);

        parent::tearDown();
    }

    /**
     * @see testRegisterAddsPhpRuntimeInfoToRootSpanOnEvent
     */
    public static function provideEventData(): iterable
    {
        yield 'command starting' => [CommandStarting::class];
        yield 'job processing' => [JobProcessing::class];
    }

    /**
     * @see testRegisterPrependsMiddlewareToLaravelHttpKernel
     */
    public static function provideKernelResolvedBeforeRegisterData(): iterable
    {
        yield 'kernel resolved before register, as in HTTP requests' => [true];
        yield 'kernel resolved after register' => [false];
    }

    public function testAppMetricClientUsesDedicatedLoggerInsteadOfApplicationLogger(): void
    {
        $app = $this->registerLumenProvider([
            'easy-serverless.datadog.enabled' => false,
        ]);
        $applicationLogger = new NullLogger();
        $app->instance(LoggerInterface::class, $applicationLogger);

        $appMetricLogger = $app->make(ConfigServiceId::AppMetricLogger->value);
        $appMetricClient = $app->make(AppMetricClientInterface::class);

        self::assertInstanceOf(Logger::class, $appMetricLogger);
        self::assertSame(AppMetricLoggerFactory::LOGGER_NAME, $appMetricLogger->getName());
        self::assertSame(
            $appMetricLogger,
            (new ReflectionProperty(AppMetricClient::class, 'logger'))->getValue($appMetricClient)
        );
    }

    #[RunInSeparateProcess]
    public function testRegisterAddsPhpRuntimeInfoToRootSpanInLumenConsole(): void
    {
        // Lumen does not dispatch CommandStarting when running in console, which is the case in PHPUnit
        $this->requireDdTraceFixture();

        $this->registerLumenProvider();

        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
    }

    #[DataProvider('provideEventData')]
    #[RunInSeparateProcess]
    public function testRegisterAddsPhpRuntimeInfoToRootSpanOnEvent(string $event): void
    {
        $this->requireDdTraceFixture();
        $app = $this->registerLumenProvider();
        $rootSpan = root_span();
        self::assertNotNull($rootSpan);
        // Clear what was added on register in console, to only see what the event adds
        $rootSpan->meta = [];

        $app->make('events')
            ->dispatch($event);

        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
    }

    public function testRegisterDoesNotRegisterClassMapWarmerWhenDisabled(): void
    {
        $app = $this->registerLumenProvider([
            'easy-serverless.snap_start.enabled' => true,
            'easy-serverless.snap_start.warmers.class_map.enabled' => false,
        ]);
        $expectedWarmers = [
            DatabaseConnectionsWarmer::class => $app->make(DatabaseConnectionsWarmer::class),
        ];

        $snapStartLifecycleSubscriber = $app->make(SnapStartLifecycleSubscriber::class);

        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertFalse($app->bound(ClassMapWarmer::class));
        self::assertSame(
            $expectedWarmers,
            \array_map(static fn (Closure $warmer): object => $warmer(), \iterator_to_array($warmers))
        );
    }

    public function testRegisterDoesNotRegisterSnapStartByDefault(): void
    {
        $app = $this->getApplication([], self::getConfig());

        $app->register(EasyServerlessServiceProvider::class);

        self::assertFalse($app->bound(SnapStartLifecycleSubscriber::class));
        self::assertFalse($app->bound(ClassMapWarmer::class));
        self::assertFalse($app->bound(DatabaseConnectionsWarmer::class));
    }

    #[RunInSeparateProcess]
    public function testRegisterDoesNotRegisterSnapStartInSnapStartLambdaWhenDisabled(): void
    {
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=snap-start');
        $app = $this->getApplication([], self::getConfig());

        $app->register(EasyServerlessServiceProvider::class);

        /** @var \Bref\Listener\BrefEventSubscriber[] $subscribers */
        $subscribers = (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events());
        self::assertFalse($app->bound(SnapStartLifecycleSubscriber::class));
        self::assertSame([], \array_filter(
            $subscribers,
            static fn (BrefEventSubscriber $subscriber): bool => $subscriber instanceof LazySnapStartLifecycleSubscriber
        ));
    }

    #[RunInSeparateProcess]
    public function testRegisterDoesNotSubscribeSnapStartSubscriberOutsideSnapStartLambda(): void
    {
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=on-demand');
        $app = $this->getApplication([], [
            ...self::getConfig(),
            'easy-serverless.snap_start.enabled' => true,
        ]);

        $app->register(EasyServerlessServiceProvider::class);

        /** @var \Bref\Listener\BrefEventSubscriber[] $subscribers */
        $subscribers = (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events());
        self::assertTrue($app->bound(SnapStartLifecycleSubscriber::class));
        self::assertSame([], \array_filter(
            $subscribers,
            static fn (BrefEventSubscriber $subscriber): bool => $subscriber instanceof LazySnapStartLifecycleSubscriber
        ));
    }

    #[RunInSeparateProcess]
    public function testRegisterDoesNothingWhenDatadogDisabled(): void
    {
        $this->requireDdTraceFixture();

        $app = $this->registerLumenProvider([
            'easy-serverless.datadog.enabled' => false,
        ]);

        self::assertSame([], root_span()?->meta);
        self::assertFalse($app->make('events')->hasListeners(JobProcessing::class));
        self::assertSame([], (new ReflectionProperty($app, 'middleware'))->getValue($app));
    }

    public function testRegisterPassesClassMapNamespacesToClassMapWarmer(): void
    {
        $app = $this->getApplication([], [
            ...self::getConfig(),
            'easy-serverless.snap_start.enabled' => true,
            'easy-serverless.snap_start.warmers.class_map.namespaces' => ['Acme\\Billing\\', 'Shared\\'],
        ]);
        $app->register(EasyServerlessServiceProvider::class);
        $expectedNamespaces = ['Acme\\Billing\\', 'Shared\\'];

        $classMapWarmer = $app->make(ClassMapWarmer::class);

        self::assertSame(
            $expectedNamespaces,
            (new ReflectionProperty(ClassMapWarmer::class, 'namespaces'))->getValue($classMapWarmer)
        );
    }

    #[RunInSeparateProcess]
    public function testRegisterPrependsMiddlewareRunningBeforeLumenGlobalMiddlewareReturningEarly(): void
    {
        $this->requireDdTraceFixture();
        $app = $this->getApplication([], self::getConfig());
        // Application middleware is usually registered before the providers in bootstrap/app.php
        $app->middleware([ShortCircuitMiddlewareStub::class]);
        $app->register(EasyServerlessServiceProvider::class);
        $rootSpan = root_span();
        self::assertNotNull($rootSpan);
        // Clear what was added on register in console, to only see what the middleware adds
        $rootSpan->meta = [];

        $response = $app->handle(Request::create('/does-not-exist'));

        self::assertSame(ShortCircuitMiddlewareStub::RESPONSE_CONTENT, $response->getContent());
        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
        self::assertSame(
            [PhpRuntimeInfoMiddleware::class, ShortCircuitMiddlewareStub::class],
            (new ReflectionProperty($app, 'middleware'))->getValue($app)
        );
    }

    #[DataProvider('provideKernelResolvedBeforeRegisterData')]
    #[RunInSeparateProcess]
    public function testRegisterPrependsMiddlewareToLaravelHttpKernel(bool $kernelResolvedBeforeRegister): void
    {
        if (\class_exists(FoundationApplication::class) === false) {
            self::markTestSkipped('laravel/framework is not installed');
        }

        $app = new FoundationApplication(__DIR__);
        $app->instance('config', new Repository());
        \config(self::getConfig());
        $app->singleton(HttpKernel::class, FoundationHttpKernel::class);
        // As ApplicationBuilder::withMiddleware() does in bootstrap/app.php, before the providers are registered
        $app->afterResolving(HttpKernel::class, static function (FoundationHttpKernel $httpKernel): void {
            $httpKernel->pushMiddleware(ShortCircuitMiddlewareStub::class);
        });

        if ($kernelResolvedBeforeRegister) {
            $app->make(HttpKernel::class);
        }

        $app->register(EasyServerlessServiceProvider::class);

        /** @var \Illuminate\Foundation\Http\Kernel $httpKernel */
        $httpKernel = $app->make(HttpKernel::class);
        self::assertSame(
            [PhpRuntimeInfoMiddleware::class, ShortCircuitMiddlewareStub::class],
            $httpKernel->getGlobalMiddleware()
        );
    }

    public function testRegisterRegistersSnapStartSubscriberRunningNextWarmersWhenTaggedWarmerCannotBeCreated(): void
    {
        $app = $this->registerLumenProvider([
            'easy-serverless.snap_start.enabled' => true,
            'easy-serverless.snap_start.warmers.class_map.enabled' => false,
        ]);
        $exception = new RuntimeException('Warmer cannot be created.');
        $app->bind(SnapStartWarmerStub::class, static fn (): never => throw $exception);
        $app->tag([SnapStartWarmerStub::class], [SnapStartTag::Warmer->value]);
        $expectedContext = [
            'exception' => $exception,
            'warmer' => SnapStartWarmerStub::class,
        ];
        $logHandler = new TestHandler();
        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($app->make(SnapStartLifecycleSubscriber::class));
        // The warmers of the registered subscriber, run with a logger and a marker path of the test
        $sut = new SnapStartLifecycleSubscriber(
            warmers: $warmers,
            afterRestoreHooks: [],
            logger: new Logger('test', [$logHandler]),
            markerPath: $this->markerPath,
        );

        $sut->afterStartup();

        self::assertCount(2, $logHandler->getRecords());
        self::assertSame(Level::Error, $logHandler->getRecords()[0]->level);
        self::assertSame('SnapStart warmer could not be created', $logHandler->getRecords()[0]->message);
        self::assertSame($expectedContext, $logHandler->getRecords()[0]->context);
        self::assertSame('SnapStart warm-up completed', $logHandler->getRecords()[1]->message);
        self::assertIsArray($logHandler->getRecords()[1]->context['warmers']);
        self::assertSame(
            [DatabaseConnectionsWarmer::class],
            \array_keys($logHandler->getRecords()[1]->context['warmers'])
        );
    }

    public function testRegisterRegistersSnapStartSubscriberWithWarmersAndAfterRestoreHooks(): void
    {
        $app = $this->registerLumenProvider([
            'easy-serverless.snap_start.enabled' => true,
        ]);
        // Tagged after this package provider is registered, as the application providers do
        $snapStartWarmer = new SnapStartWarmerStub();
        $app->instance(SnapStartWarmerStub::class, $snapStartWarmer);
        $app->tag([SnapStartWarmerStub::class], [SnapStartTag::Warmer->value]);
        $afterRestoreHook = new AfterRestoreHookStub();
        $app->instance(AfterRestoreHookStub::class, $afterRestoreHook);
        $app->tag([AfterRestoreHookStub::class], [SnapStartTag::AfterRestoreHook->value]);
        // In this order, keyed by service, the DatabaseConnectionsWarmer last
        $expectedWarmers = [
            ClassMapWarmer::class => $app->make(ClassMapWarmer::class),
            SnapStartWarmerStub::class => $snapStartWarmer,
            DatabaseConnectionsWarmer::class => $app->make(DatabaseConnectionsWarmer::class),
        ];
        $expectedAfterRestoreHooks = [
            AfterRestoreHookStub::class => $afterRestoreHook,
        ];

        $snapStartLifecycleSubscriber = $app->make(SnapStartLifecycleSubscriber::class);

        /** @var iterable<string, \Closure(): object> $warmers */
        $warmers = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'warmers'))
            ->getValue($snapStartLifecycleSubscriber);
        /** @var iterable<string, \Closure(): object> $afterRestoreHooks */
        $afterRestoreHooks = (new ReflectionProperty(SnapStartLifecycleSubscriber::class, 'afterRestoreHooks'))
            ->getValue($snapStartLifecycleSubscriber);
        self::assertSame(
            $expectedWarmers,
            \array_map(static fn (Closure $warmer): object => $warmer(), \iterator_to_array($warmers))
        );
        self::assertSame(
            $expectedAfterRestoreHooks,
            \array_map(
                static fn (Closure $afterRestoreHook): object => $afterRestoreHook(),
                \iterator_to_array($afterRestoreHooks)
            )
        );
    }

    #[RunInSeparateProcess]
    public function testRegisterSubscribesLazySnapStartSubscriberInSnapStartLambda(): void
    {
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        \putenv('AWS_LAMBDA_INITIALIZATION_TYPE=snap-start');
        $app = $this->getApplication([], [
            ...self::getConfig(),
            'easy-serverless.snap_start.enabled' => true,
        ]);

        $app->register(EasyServerlessServiceProvider::class);

        /** @var \Bref\Listener\BrefEventSubscriber[] $subscribers */
        $subscribers = (new ReflectionProperty(EventDispatcher::class, 'subscribers'))->getValue(Bref::events());
        // The Bref Laravel bridge subscribes its own subscriber when it is autoloaded
        $lazySubscribers = \array_values(\array_filter(
            $subscribers,
            static fn (BrefEventSubscriber $subscriber): bool => $subscriber instanceof LazySnapStartLifecycleSubscriber
        ));
        self::assertCount(1, $lazySubscribers);
        self::assertSame(
            $app,
            (new ReflectionProperty(LazySnapStartLifecycleSubscriber::class, 'container'))
                ->getValue($lazySubscribers[0])
        );
        // Resolved on afterStartup only, once the application providers tagged their warmers and hooks
        self::assertFalse($app->resolved(SnapStartLifecycleSubscriber::class));
    }

    /**
     * @return array<string, bool>
     */
    private static function getConfig(): array
    {
        // Health routes rely on the Route facade, which is not available in a bare Lumen application
        return [
            'easy-serverless.health.enabled' => false,
        ];
    }

    /**
     * @param array<string, bool> $config
     */
    private function registerLumenProvider(array $config = []): LumenApplication
    {
        $app = $this->getApplication([], [...self::getConfig(), ...$config]);
        $app->register(EasyServerlessServiceProvider::class);

        return $app;
    }

    private function requireDdTraceFixture(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../Fixture/ddtrace/ddtrace.php';
    }
}
