<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Laravel;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();
        $requestIdProvider = $app->make(RequestIdProviderInterface::class);

        self::assertTrue(Uuid::isValid($requestIdProvider->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdProvider->getRequestId()));
    }
}
