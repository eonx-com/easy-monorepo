<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Laravel;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

final class EasyLoggingServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(LoggerFactoryInterface::class, $app->get(LoggerFactoryInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get('logger'));
    }
}
