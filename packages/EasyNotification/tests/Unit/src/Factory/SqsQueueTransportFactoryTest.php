<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Factory;

use EonX\EasyNotification\Factory\SqsQueueTransportFactory;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\Transport\SqsQueueTransport;
use EonX\EasyNotification\ValueObject\Config;

final class SqsQueueTransportFactoryTest extends AbstractUnitTestCase
{
    public function testCreate(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
        $transportFactory = new SqsQueueTransportFactory();

        self::assertInstanceOf(SqsQueueTransport::class, $transportFactory->create($config));
    }
}
