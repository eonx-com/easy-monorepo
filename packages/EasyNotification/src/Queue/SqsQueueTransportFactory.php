<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Queue;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\QueueTransportFactoryInterface;
use EonX\EasyNotification\Interfaces\QueueTransportInterface;

final class SqsQueueTransportFactory implements QueueTransportFactoryInterface
{
    public function create(ConfigInterface $config): QueueTransportInterface
    {
        return new SqsQueueTransport(new SqsClient([
            'region' => $config->getQueueRegion(),
            'version' => 'latest',
        ]));
    }
}
