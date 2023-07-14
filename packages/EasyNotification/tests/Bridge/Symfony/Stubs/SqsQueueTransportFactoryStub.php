<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Symfony\Stubs;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\QueueTransportFactoryInterface;
use EonX\EasyNotification\Interfaces\QueueTransportInterface;
use EonX\EasyNotification\Queue\SqsQueueTransport;

final class SqsQueueTransportFactoryStub implements QueueTransportFactoryInterface
{
    public function __construct(
        private SqsClient $sqsClient,
    ) {
    }

    public function create(ConfigInterface $config): QueueTransportInterface
    {
        return new SqsQueueTransport($this->sqsClient);
    }
}
