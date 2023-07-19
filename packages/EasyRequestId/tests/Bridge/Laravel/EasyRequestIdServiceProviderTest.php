<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Laravel;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Uid\Uuid;

final class EasyRequestIdServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();
        $requestIdService = $app->make(RequestIdServiceInterface::class);

        self::assertTrue(Uuid::isValid($requestIdService->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdService->getRequestId()));
    }
}
