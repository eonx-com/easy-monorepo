<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle;

use Bref\Symfony\Messenger\BrefMessengerBundle;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use EonX\EasyServerless\Datadog\Listener\PhpRuntimeInfoListener;
use EonX\EasyServerless\Messenger\Listener\ResetServicesListener;
use EonX\EasyServerless\Monolog\Processor\PhpSourceProcessor;
use EonX\EasyServerless\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Logger;
use Psr\Log\NullLogger;
use ReflectionProperty;

final class EasyServerlessBundleTest extends AbstractSymfonyTestCase
{
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
}
