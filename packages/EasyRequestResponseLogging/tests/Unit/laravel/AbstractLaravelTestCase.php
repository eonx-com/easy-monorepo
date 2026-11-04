<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Unit\Laravel;

use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Laravel\EasyRequestResponseLoggingServiceProvider;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelInterface;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

abstract class AbstractLaravelTestCase extends TestCase
{
    protected TestHandler $logHandler;

    /**
     * @param class-string<\Illuminate\Support\ServiceProvider>[]|null $providers
     */
    protected function createApplication(array $config = [], ?array $providers = null): Application
    {
        $app = $this->createLaravelApplication();
        $app->make('config')
            ->set(\array_merge(['easy-request-response-logging.enabled' => true], $config));

        $providers ??= [
            EasyRandomServiceProvider::class,
            EasyRequestIdServiceProvider::class,
            EasyUtilsServiceProvider::class,
        ];

        foreach ([...$providers, EasyRequestResponseLoggingServiceProvider::class] as $provider) {
            $app->register($provider);
        }

        $this->logHandler = new TestHandler();

        if ($app->bound(ConfigServiceId::Logger->value)) {
            $app->instance(ConfigServiceId::Logger->value, new Logger('test', [$this->logHandler]));
        }

        return $app;
    }

    protected function createLaravelApplication(): Application
    {
        $app = new Application(__DIR__);
        $app->instance('config', new ConfigRepository());
        $app->instance(LoggerInterface::class, new NullLogger());
        $exceptionHandler = new class implements ExceptionHandler {
            public function render($request, Throwable $e): Response
            {
                return new Response(
                    status: $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500
                );
            }

            public function renderForConsole($output, Throwable $e): void {}

            public function report(Throwable $e): void {}

            public function shouldReport(Throwable $e): bool
            {
                return false;
            }
        };
        $app->instance(ExceptionHandler::class, $exceptionHandler);
        // No bootstrappers: the application is configured and booted by the test
        $app->singleton(
            HttpKernelInterface::class,
            static fn(Application $app): HttpKernelInterface => new class (
                $app,
                $app->make('router')
            ) extends HttpKernel {
                protected function bootstrappers(): array
                {
                    return [];
                }
            }
        );

        return $app;
    }

    protected function handle(Application $app, Request $request): Response
    {
        $app->boot();
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $app->make(HttpKernelInterface::class);

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }
}
