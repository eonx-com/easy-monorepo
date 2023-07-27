<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface QueueTransportFactoryInterface
{
    public function create(ConfigInterface $config): QueueTransportInterface;
}
