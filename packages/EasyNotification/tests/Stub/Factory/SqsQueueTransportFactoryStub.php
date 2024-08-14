<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stub\Factory;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Factory\QueueTransportFactoryInterface;
use EonX\EasyNotification\Transport\QueueTransportInterface;
use EonX\EasyNotification\Transport\SqsQueueTransport;
use EonX\EasyNotification\ValueObject\Config;

final readonly class SqsQueueTransportFactoryStub implements QueueTransportFactoryInterface
{
    public function __construct(
        private SqsClient $sqsClient,
    ) {
    }

    public function create(Config $config): QueueTransportInterface
    {
        return new SqsQueueTransport($this->sqsClient);
    }
}
