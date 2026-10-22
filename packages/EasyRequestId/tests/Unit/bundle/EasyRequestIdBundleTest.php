<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Bundle;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\EasyLogging\Processor\RequestIdProcessor;
use EonX\EasyRequestId\Tests\Stub\Kernel\KernelStub;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdBundleTest extends AbstractSymfonyTestCase
{
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
