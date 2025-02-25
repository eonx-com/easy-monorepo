<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Transport;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Message\QueueMessageInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SqsQueueTransport implements QueueTransportInterface
{
    public function __construct(
        private SqsClient $sqs,
    ) {
    }

    public function send(QueueMessageInterface $queueMessage): void
    {
        $queueUrl = $queueMessage->getQueueUrl();
        $args = [
            'MessageAttributes' => $this->formatHeaders($queueMessage->getHeaders()),
            'MessageBody' => $queueMessage->getBody(),
            'QueueUrl' => $queueUrl,
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
     * @return array
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
