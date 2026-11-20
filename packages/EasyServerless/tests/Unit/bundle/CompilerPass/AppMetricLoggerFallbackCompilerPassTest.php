<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle\CompilerPass;

use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\Bundle\CompilerPass\AppMetricLoggerFallbackCompilerPass;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AppMetricLoggerFallbackCompilerPassTest extends AbstractUnitTestCase
{
    public function testClientUsesApplicationLoggerWhenDedicatedLoggerIsNotAvailable(): void
    {
        $container = $this->getContainerBuilder(withDedicatedLogger: false);
        $container->register(LoggerInterface::class, NullLogger::class)
            ->setPublic(true);

        $container->compile();

        self::assertSame($container->get(LoggerInterface::class), $this->getClientLogger($container));
    }

    public function testClientUsesApplicationOverrideWhenDedicatedLoggerIsNotAvailable(): void
    {
        $container = $this->getContainerBuilder(withDedicatedLogger: false);
        $container->register(LoggerInterface::class, NullLogger::class)
            ->setPublic(true);
        $container->register(ConfigServiceId::AppMetricLogger->value, NullLogger::class)
            ->setPublic(true);

        $container->compile();

        $clientLogger = $this->getClientLogger($container);
        self::assertSame($container->get(ConfigServiceId::AppMetricLogger->value), $clientLogger);
        self::assertNotSame($container->get(LoggerInterface::class), $clientLogger);
    }

    public function testClientUsesDedicatedLoggerWhenAvailable(): void
    {
        $container = $this->getContainerBuilder(withDedicatedLogger: true);
        $container->register(LoggerInterface::class, NullLogger::class);

        $container->compile();

        self::assertInstanceOf(Logger::class, $this->getClientLogger($container));
    }

    public function testClientUsesNullLoggerWhenNoLoggerIsRegistered(): void
    {
        $container = $this->getContainerBuilder(withDedicatedLogger: false);

        $container->compile();

        self::assertFalse($container->has(ConfigServiceId::AppMetricLogger->value));
        self::assertInstanceOf(NullLogger::class, $this->getClientLogger($container));
    }

    private function getClientLogger(ContainerBuilder $container): LoggerInterface
    {
        $logger = (new ReflectionProperty(AppMetricClient::class, 'logger'))
            ->getValue($container->get(AppMetricClientInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $logger);

        return $logger;
    }

    private function getContainerBuilder(bool $withDedicatedLogger): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter(ConfigParam::AppMetricNamespace->value, null);
        (new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../../bundle/config')))
            ->load('app_metric.php');
        $container->getDefinition(AppMetricClientInterface::class)
            ->setPublic(true);

        // Simulate Monolog not being installed
        if ($withDedicatedLogger === false) {
            $container->removeDefinition(ConfigServiceId::AppMetricLogger->value);
        }

        $container->addCompilerPass(new AppMetricLoggerFallbackCompilerPass());

        return $container;
    }
}
