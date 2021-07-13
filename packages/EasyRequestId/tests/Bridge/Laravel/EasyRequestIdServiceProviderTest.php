<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Bridge\Laravel;

use EonX\EasyRequestId\Bridge\Laravel\Bus\DispatcherDecorator;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Ramsey\Uuid\Uuid;

final class EasyRequestIdServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();
        $dispatcher = $app->make(QueueingDispatcher::class);
        $requestIdService = $app->make(RequestIdServiceInterface::class);

        self::assertInstanceOf(DispatcherDecorator::class, $dispatcher);
        self::assertTrue(Uuid::isValid($requestIdService->getCorrelationId()));
        self::assertTrue(Uuid::isValid($requestIdService->getRequestId()));
    }
}
