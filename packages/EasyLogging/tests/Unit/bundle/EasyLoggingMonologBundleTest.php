<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Bundle;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Tests\Stub\Kernel\MonologKernelStub;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class EasyLoggingMonologBundleTest extends AbstractUnitTestCase
{
    public function testLoggerIsOwnedBySymfonyMonologBundle(): void
    {
        $kernel = new MonologKernelStub([__DIR__ . '/../../Fixture/config/use_symfony_monolog_bundle.php']);
        $kernel->boot();
        $container = $kernel->getContainer();

        self::assertTrue((bool)$container->getParameter(ConfigParam::UseSymfonyMonologBundle->value));

        // The EasyLogging channel-replacement pass steps aside, so "logger" is the symfony/monolog-bundle logger,
        // recognisable by the TestHandler declared in the fixture (the EasyLogging factory never uses it)
        $logger = $container->get('logger');
        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));

        $hasTestHandler = false;
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof TestHandler) {
                $hasTestHandler = true;

                break;
            }
        }

        self::assertTrue($hasTestHandler, 'The "logger" service must be provided by symfony/monolog-bundle.');

        // The LoggerFactory remains available (deprecated) for backward compatibility
        self::assertInstanceOf(LoggerFactoryInterface::class, $container->get(LoggerFactoryInterface::class));
    }
}
