<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Laravel;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();
        $requestId = $app->make(RequestIdInterface::class);

        self::assertTrue(Uuid::isValid($requestId->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestId->getRequestId()));
    }
}
