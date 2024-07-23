<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Bundle;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()->getContainer();
        $requestIdProvider = $container->get(RequestIdProviderInterface::class);

        self::assertTrue(Uuid::isValid($requestIdProvider->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdProvider->getRequestId()));
    }
}
