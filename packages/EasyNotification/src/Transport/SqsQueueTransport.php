<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Transport;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Message\QueueMessageInterface;

final class SqsQueueTransport implements QueueTransportInterface
{
    public function __construct(
        private SqsClient $sqs,
    ) {
    }

    public function send(QueueMessageInterface $queueMessage): void
    {
        $this->sqs->sendMessage([
            'MessageAttributes' => $this->formatHeaders($queueMessage->getHeaders()),
            'MessageBody' => $queueMessage->getBody(),
            'QueueUrl' => $queueMessage->getQueueUrl(),
        ]);
    }

    /**
     * @param string[] $headers
     */
    private function formatHeaders(array $headers): array
    {
        $sqsHeaders = [];

        foreach ($headers as $name => $value) {
            $sqsHeaders[$name] = [
                'DataType' => 'String',
                'StringValue' => $value,
            ];
        }

        return $sqsHeaders;
    }
}
