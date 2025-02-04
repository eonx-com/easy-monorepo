<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Queue;

use EonX\EasyNotification\Queue\QueueMessage;
use EonX\EasyNotification\Queue\SqsQueueTransport;
use EonX\EasyNotification\Tests\AbstractTestCase;
use EonX\EasyNotification\Tests\Stubs\SqsClientStub;

final class SqsQueueTransportTest extends AbstractTestCase
{
    public function testSend(): void
    {
        $stub = new SqsClientStub();
        $queueMessage = (new QueueMessage())
            ->addHeader('my-header', 'my-value')
            ->setBody('my-body')
            ->setQueueUrl(static::$defaultConfig['queueUrl']);

        (new SqsQueueTransport($stub))->send($queueMessage);

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
        $queueMessage = (new QueueMessage())
            ->addHeader('my-header', 'my-value')
            ->setBody('my-body')
            ->setQueueUrl($queueUrl);

        (new SqsQueueTransport($stub))->send($queueMessage);

        self::assertNotNull($stub->getCalls()[0]['MessageDeduplicationId']);
        self::assertNotNull($stub->getCalls()[0]['MessageGroupId']);
    }
}
