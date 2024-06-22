<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Bundle;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default.php'])->getContainer();
        $requestId = $container->get(RequestIdInterface::class);

        self::assertTrue(Uuid::isValid($requestId->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestId->getRequestId()));
    }
}
