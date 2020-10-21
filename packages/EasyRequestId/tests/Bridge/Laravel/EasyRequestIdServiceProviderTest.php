<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Laravel;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Ramsey\Uuid\Uuid;

final class EasyRequestIdServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $requestIdService = $this->getApplication()
            ->make(RequestIdServiceInterface::class);

        self::assertTrue(Uuid::isValid($requestIdService->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdService->getRequestId()));
    }
}
