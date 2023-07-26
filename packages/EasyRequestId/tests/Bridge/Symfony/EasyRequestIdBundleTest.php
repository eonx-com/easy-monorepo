<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Symfony;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/config/default.yaml'])->getContainer();
        $requestIdService = $container->get(RequestIdServiceInterface::class);

        self::assertTrue(Uuid::isValid($requestIdService->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdService->getRequestId()));
    }
}
