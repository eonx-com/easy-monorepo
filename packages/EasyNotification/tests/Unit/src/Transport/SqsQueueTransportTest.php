<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Transport;

use EonX\EasyNotification\Message\QueueMessage;
use EonX\EasyNotification\Tests\Stub\SqsClient\SqsClientStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\Transport\SqsQueueTransport;

final class SqsQueueTransportTest extends AbstractUnitTestCase
{
    public function testSend(): void
    {
        $stub = new SqsClientStub();
        $queueMessage = new QueueMessage()
            ->addHeader('my-header', 'my-value')
            ->setBody('my-body')
            ->setQueueUrl(static::$defaultConfig['queueUrl']);

        new SqsQueueTransport($stub)
->send($queueMessage);

        $expected = [
            'QueueUrl' => static::$defaultConfig['queueUrl'],
            'MessageAttributes' => [
                'my-header' => [
                    'DataType' => 'String',
                    'StringValue' => 'my-value',
                ],
            ],
            'MessageBody' => 'my-body',
        ];

        self::assertEquals($expected, $stub->getCalls()[0]);
    }

    public function testSendFifo(): void
    {
        $queueUrl = 'https://sqs.my-queue.fifo';
        $stub = new SqsClientStub();
        $queueMessage = new QueueMessage()
            ->addHeader('my-header', 'my-value')
            ->setBody('my-body')
            ->setQueueUrl($queueUrl);

        new SqsQueueTransport($stub)
->send($queueMessage);

        self::assertNotNull($stub->getCalls()[0]['MessageDeduplicationId']);
        self::assertNotNull($stub->getCalls()[0]['MessageGroupId']);
    }
}
