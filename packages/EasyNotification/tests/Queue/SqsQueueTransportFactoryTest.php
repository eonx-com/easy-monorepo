<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Queue;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Queue\SqsQueueTransport;
use EonX\EasyNotification\Queue\SqsQueueTransportFactory;
use EonX\EasyNotification\Tests\AbstractTestCase;

final class SqsQueueTransportFactoryTest extends AbstractTestCase
{
    public function testCreate(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
        $transportFactory = new SqsQueueTransportFactory();

        self::assertInstanceOf(SqsQueueTransport::class, $transportFactory->create($config));
    }
}
