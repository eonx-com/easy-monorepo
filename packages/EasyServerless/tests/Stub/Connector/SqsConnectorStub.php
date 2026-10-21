<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Connector;

use Aws\Sqs\SqsClient;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\SqsQueue;

final readonly class SqsConnectorStub implements ConnectorInterface
{
    public function __construct(
        private SqsClient $sqsClient,
    ) {}

    public function connect(array $config): Queue
    {
        return new SqsQueue($this->sqsClient, $config['queue'], $config['prefix'] ?? '');
    }
}
