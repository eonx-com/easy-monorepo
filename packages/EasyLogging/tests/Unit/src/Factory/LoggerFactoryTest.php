<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Factory;

use EonX\EasyLogging\Factory\LoggerFactory;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use Monolog\Handler\NullHandler;

final class LoggerFactoryTest extends AbstractUnitTestCase
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
