<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Unit\Laravel;

use EonX\EasyLogging\Laravel\EasyLoggingServiceProvider;
use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider;
use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use EonX\EasyRequestResponseLogging\Laravel\EasyRequestResponseLoggingServiceProvider;
use EonX\EasyRequestResponseLogging\Laravel\Middleware\RequestResponseLoggingMiddleware;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use LogicException;
use Monolog\Logger;

final class EasyRequestResponseLoggingServiceProviderTest extends AbstractLumenTestCase
{
    public function testBootThrowsExceptionWhenEasyRequestIdServiceProviderIsNotRegistered(): void
    {
        $app = $this->createApplication(providers: [EasyUtilsServiceProvider::class]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EasyRequestIdServiceProvider is not registered.');

        $app->boot();
    }

    public function testBootThrowsExceptionWhenSensitiveDataSanitizerIsDisabled(): void
    {
        $app = $this->createApplication(
            config: [
                'easy-utils.sensitive_data.enabled' => false,
            ]
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('SensitiveDataSanitizerInterface is not registered');

        $app->boot();
    }

    public function testMiddlewareIsNotRegisteredByDefault(): void
    {
        $app = $this->createLumenApplication();
        $app->register(EasyRequestResponseLoggingServiceProvider::class);
        $app->boot();

        self::assertFalse($app->bound(RequestResponseLogger::class));
        self::assertFalse($app->bound(RequestResponseLoggingMiddleware::class));
    }

    public function testMiddlewareIsRegisteredWhenEnabled(): void
    {
        $app = $this->createApplication();
        $app->boot();

        self::assertInstanceOf(RequestResponseLogger::class, $app->make(RequestResponseLogger::class));
        self::assertInstanceOf(
            RequestResponseLoggingMiddleware::class,
            $app->make(RequestResponseLoggingMiddleware::class)
        );
    }

    public function testUsesEasyLoggingLoggerWhenEasyLoggingServiceProviderIsRegistered(): void
    {
        $app = $this->createLumenApplication();
        $app->make('config')
            ->set('easy-request-response-logging.enabled', true);
        $app->register(EasyLoggingServiceProvider::class);
        $app->register(EasyRandomServiceProvider::class);
        $app->register(EasyRequestIdServiceProvider::class);
        $app->register(EasyUtilsServiceProvider::class);
        $app->register(EasyRequestResponseLoggingServiceProvider::class);

        $logger = $app->make(ConfigServiceId::Logger->value);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame(BundleParam::LogChannel->value, $logger->getName());
    }
}
