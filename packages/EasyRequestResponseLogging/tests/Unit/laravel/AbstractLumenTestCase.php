<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Unit\Laravel;

use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Laravel\EasyRequestResponseLoggingServiceProvider;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Laravel\Lumen\Application;
use Laravel\Lumen\Exceptions\Handler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractLumenTestCase extends TestCase
{
    protected TestHandler $logHandler;

    private int $applicationCount = 0;

    protected function tearDown(): void
    {
        // Lumen registers its own error and exception handlers in the application constructor
        for (; $this->applicationCount > 0; $this->applicationCount--) {
            \restore_error_handler();
            \restore_exception_handler();
        }

        parent::tearDown();
    }

    /**
     * @param class-string<\Illuminate\Support\ServiceProvider>[]|null $providers
     */
    protected function createApplication(array $config = [], ?array $providers = null): Application
    {
        $app = $this->createLumenApplication();
        $app->singleton(ExceptionHandler::class, Handler::class);
        // Prevents the exception handler from writing log files into the test directory
        $app->instance(LoggerInterface::class, new NullLogger());

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

    protected function createLumenApplication(): Application
    {
        $this->applicationCount++;

        return new Application(__DIR__);
    }
}
