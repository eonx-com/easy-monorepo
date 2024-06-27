<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Factory;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Transport\QueueTransportInterface;
use EonX\EasyNotification\Transport\SqsQueueTransport;
use EonX\EasyNotification\ValueObject\ConfigInterface;

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
