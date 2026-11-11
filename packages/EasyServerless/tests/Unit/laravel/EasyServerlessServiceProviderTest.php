<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use EonX\EasyServerless\Laravel\EasyServerlessServiceProvider;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Tests\Stub\Middleware\ShortCircuitMiddlewareStub;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Queue\Events\JobProcessing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

use function DDTrace\root_span;

final class EasyServerlessServiceProviderTest extends AbstractLaravelTestCase
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
     * @see testRegisterPrependsMiddlewareToHttpKernel
     */
    public static function provideKernelResolvedBeforeRegisterData(): iterable
    {
        yield 'kernel resolved before register, as in HTTP requests' => [true];
        yield 'kernel resolved after register' => [false];
    }

    #[DataProvider('provideEventData')]
    #[RunInSeparateProcess]
    public function testRegisterAddsPhpRuntimeInfoToRootSpanOnEvent(string $event): void
    {
        $this->requireDdTraceFixture();
        $app = $this->registerProvider();
        $rootSpan = root_span();
        self::assertNotNull($rootSpan);
        self::assertSame([], $rootSpan->meta);

        $app->make('events')
            ->dispatch($event);

        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
    }

    #[RunInSeparateProcess]
    public function testRegisterDoesNothingWhenDatadogDisabled(): void
    {
        $this->requireDdTraceFixture();

        $app = $this->registerProvider([
            'datadog' => [
                'enabled' => false,
            ],
        ]);

        /** @var \Illuminate\Foundation\Http\Kernel $httpKernel */
        $httpKernel = $app->make(HttpKernel::class);
        self::assertFalse($app->make('events')->hasListeners(JobProcessing::class));
        self::assertSame([], $httpKernel->getGlobalMiddleware());
        self::assertSame([], root_span()?->meta);
    }

    #[DataProvider('provideKernelResolvedBeforeRegisterData')]
    public function testRegisterPrependsMiddlewareToHttpKernel(bool $kernelResolvedBeforeRegister): void
    {
        $app = $this->getApplicationWithHttpKernel();
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
     * @param array<string, array<string, bool>> $config
     */
    private function getApplicationWithHttpKernel(array $config = []): Application
    {
        // The health routes are covered by their own tests
        $app = $this->getApplication([
            'easy-serverless' => [
                'health' => [
                    'enabled' => false,
                ],
                ...$config,
            ],
        ]);
        $app->singleton(HttpKernel::class, FoundationHttpKernel::class);

        return $app;
    }

    /**
     * @param array<string, array<string, bool>> $config
     */
    private function registerProvider(array $config = []): Application
    {
        $app = $this->getApplicationWithHttpKernel($config);
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
