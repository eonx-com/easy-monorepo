<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use EonX\EasyServerless\Laravel\EasyServerlessServiceProvider;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Tests\Stub\Middleware\ShortCircuitMiddlewareStub;
use Illuminate\Config\Repository;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Lumen\Application as LumenApplication;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionProperty;

use function DDTrace\root_span;

final class EasyServerlessServiceProviderTest extends AbstractLumenTestCase
{
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
