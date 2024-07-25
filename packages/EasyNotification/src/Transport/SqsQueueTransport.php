<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Transport;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Message\QueueMessageInterface;
use SplObjectStorage;

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
     * @param \SplObjectStorage<\EonX\EasyNotification\Enum\Header, string> $headers
     */
    private function formatHeaders(SplObjectStorage $headers): array
    {
        $sqsHeaders = [];

        foreach ($headers as $header) {
            $sqsHeaders[$header->value] = [
                'DataType' => 'String',
                'StringValue' => $headers->offsetGet($header),
            ];
        }

        return $sqsHeaders;
    }
}
