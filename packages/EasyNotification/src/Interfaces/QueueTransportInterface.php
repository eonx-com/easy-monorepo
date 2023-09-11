<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface QueueTransportInterface
{
    public function send(QueueMessageInterface $queueMessage): void;
}
