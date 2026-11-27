<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Bundle;

use EonX\EasyErrorHandler\Common\Builder\AbstractErrorResponseBuilder;
use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasyRequestId\Bundle\EasyRequestIdBundle;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\EasyErrorHandler\Builder\RequestIdErrorResponseBuilder;
use EonX\EasyRequestId\EasyHttpClient\Modifier\RequestIdRequestDataModifier;
use EonX\EasyRequestId\EasyLogging\Processor\RequestIdProcessor;
use EonX\EasyRequestId\EasyWebhook\Middleware\RequestIdWebhookMiddleware;
use EonX\EasyRequestId\Tests\Stub\Kernel\KernelStub;
use EonX\EasyWebhook\Common\Middleware\AbstractConfigureOnceMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testItRegistersIntegrationOnlyWhenItsPackageIsInstalled
     */
    public static function provideIntegrationData(): iterable
    {
        yield 'EasyErrorHandler' => [RequestIdErrorResponseBuilder::class, AbstractErrorResponseBuilder::class];
        yield 'EasyHttpClient' => [RequestIdRequestDataModifier::class, RequestDataModifierInterface::class];
        yield 'EasyLogging' => [RequestIdProcessor::class, AbstractSelfConfigProvidingProcessor::class];
        yield 'EasyWebhook' => [RequestIdWebhookMiddleware::class, AbstractConfigureOnceMiddleware::class];
    }

    #[DataProvider('provideIntegrationData')]
    public function testItRegistersIntegrationOnlyWhenItsPackageIsInstalled(string $service, string $dependency): void
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.build_dir' => \sys_get_temp_dir(),
            'kernel.environment' => 'test',
        ]));

        new EasyRequestIdBundle()
->getContainerExtension()?->load([], $container);

        self::assertSame(
            \class_exists($dependency) || \interface_exists($dependency),
            $container->hasDefinition($service)
        );
    }

    public function testRequestIdProcessorIsRegisteredAsMonologProcessor(): void
    {
        $kernel = new KernelStub([__DIR__ . '/../../Fixture/config/easy_logging.php'], 'test_easy_logging');
        $kernel->boot();
        /** @var \Monolog\Logger $logger */
        $logger = $kernel->getContainer()
            ->get('logger');

        self::assertTrue(\array_any(
            $logger->getProcessors(),
            static fn(callable $processor): bool => $processor instanceof RequestIdProcessor
        ));
    }

    public function testSanity(): void
    {
        $container = $this->getKernel([
            __DIR__ . '/../../Fixture/config/minimal_config.php',
        ])
            ->getContainer();
        $requestIdProvider = $container->get(RequestIdProviderInterface::class);

        self::assertTrue(Uuid::isValid($requestIdProvider->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdProvider->getRequestId()));
    }
}
