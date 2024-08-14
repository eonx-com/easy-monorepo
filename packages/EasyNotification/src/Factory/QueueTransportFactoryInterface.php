<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Factory;

use EonX\EasyNotification\Transport\QueueTransportInterface;
use EonX\EasyNotification\ValueObject\Config;

interface QueueTransportFactoryInterface
{
    public function create(Config $config): QueueTransportInterface;
}
