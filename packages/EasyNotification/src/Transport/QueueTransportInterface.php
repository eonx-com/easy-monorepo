<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Transport;

use EonX\EasyNotification\Message\QueueMessageInterface;

interface QueueTransportInterface
{
    public function send(QueueMessageInterface $queueMessage): void;
}
