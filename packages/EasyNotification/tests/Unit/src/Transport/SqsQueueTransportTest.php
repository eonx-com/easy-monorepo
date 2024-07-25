<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Transport;

use EonX\EasyNotification\Enum\Header;
use EonX\EasyNotification\Message\QueueMessage;
use EonX\EasyNotification\Tests\Stub\Sqs\SqsClientStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\Transport\SqsQueueTransport;

final class SqsQueueTransportTest extends AbstractUnitTestCase
{
    public function testSend(): void
    {
        $stub = new SqsClientStub();
        $queueMessage = (new QueueMessage())
            ->addHeader(Header::Provider, 'some-provider')
            ->setBody('my-body')
            ->setQueueUrl(static::$defaultConfig['queueUrl']);

        (new SqsQueueTransport($stub))->send($queueMessage);

        $expected = [
            'QueueUrl' => static::$defaultConfig['queueUrl'],
            'MessageAttributes' => [
                'provider' => [
                    'DataType' => 'String',
                    'StringValue' => 'some-provider',
                ],
            ],
            'MessageBody' => 'my-body',
        ];

        self::assertEquals($expected, $stub->getCalls()[0]);
    }
}
