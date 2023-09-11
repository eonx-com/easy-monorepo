<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests;

use EonX\EasyLogging\LoggerFactory;
use Monolog\Handler\NullHandler;

final class LoggerFactoryTest extends AbstractTestCase
{
    public function testCreateDefaultEmptyLogger(): void
    {
        $loggerFactory = new LoggerFactory();

        /** @var \Monolog\Logger $logger */
        $logger = $loggerFactory->create();

        self::assertEquals('app', $logger->getName());
        self::assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
    }
}
