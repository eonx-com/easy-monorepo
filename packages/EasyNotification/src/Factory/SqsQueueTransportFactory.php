<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Factory;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Transport\QueueTransportInterface;
use EonX\EasyNotification\Transport\SqsQueueTransport;
use EonX\EasyNotification\ValueObject\Config;

final class SqsQueueTransportFactory implements QueueTransportFactoryInterface
{
    public function create(Config $config): QueueTransportInterface
    {
        return new SqsQueueTransport(new SqsClient([
            'region' => $config->getQueueRegion(),
            'version' => 'latest',
        ]));
    }
}
