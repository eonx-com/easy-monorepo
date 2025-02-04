<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;
use EonX\EasyNotification\Interfaces\QueueTransportInterface;
use Symfony\Component\Uid\Uuid;

final class SqsQueueTransport implements QueueTransportInterface
{
    /**
     * @var \Aws\Sqs\SqsClient
     */
    private $sqs;

    public function __construct(SqsClient $sqs)
    {
        $this->sqs = $sqs;
    }

    public function send(QueueMessageInterface $queueMessage): void
    {
        $queueUrl = $queueMessage->getQueueUrl();
        $args = [
            'QueueUrl' => $queueUrl,
            'MessageAttributes' => $this->formatHeaders($queueMessage->getHeaders()),
            'MessageBody' => $queueMessage->getBody(),
        ];

        if (\str_ends_with($queueUrl, '.fifo')) {
            $args['MessageDeduplicationId'] = (string)Uuid::v4();
            $args['MessageGroupId'] = (string)Uuid::v4();
        }

        $this->sqs->sendMessage($args);
    }

    /**
     * @param string[] $headers
     *
     * @return mixed[]
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
