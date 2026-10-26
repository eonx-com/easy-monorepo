<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle\SqsHandler;

use AsyncAws\Sqs\SqsClient;
use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use EonX\EasyServerless\Bundle\SqsHandler\SqsHandler;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

final class SqsHandlerTest extends AbstractUnitTestCase
{
    public function testWorkerMessageReceivedEventIsDispatchedThroughSymfonyDispatcherFallback(): void
    {
        $serializer = new PhpSerializer();
        $symfonyEventDispatcher = new EventDispatcher();
        $receivedEvent = null;
        $handledEvent = null;
        $symfonyEventDispatcher->addListener(
            WorkerMessageReceivedEvent::class,
            static function (WorkerMessageReceivedEvent $event) use (&$receivedEvent): void {
                $receivedEvent = $event;
            }
        );
        $symfonyEventDispatcher->addListener(
            WorkerMessageHandledEvent::class,
            static function (WorkerMessageHandledEvent $event) use (&$handledEvent): void {
                $handledEvent = $event;
            }
        );
        // No EasyEventDispatcher, only the Symfony one, as when EasyEventDispatcherBundle is not installed
        $handler = new SqsHandler(
            bus: new MessageBus(),
            serializer: $serializer,
            retryStrategyLocator: new ServiceLocator([]),
            sqsClient: new SqsClient(['region' => 'ap-southeast-2']),
            transportName: 'async',
            symfonyEventDispatcher: $symfonyEventDispatcher
        );
        $encoded = $serializer->encode(new Envelope(new stdClass()));

        $handler->handleSqs(
            new SqsEvent([
                'Records' => [
                    [
                        'attributes' => [
                            'ApproximateFirstReceiveTimestamp' => '0',
                            'ApproximateReceiveCount' => '1',
                            'SenderId' => 'sender-id',
                            'SentTimestamp' => '0',
                        ],
                        'awsRegion' => 'ap-southeast-2',
                        'body' => $encoded['body'],
                        'eventSource' => 'aws:sqs',
                        'eventSourceARN' => 'arn:aws:sqs:ap-southeast-2:123456789012:test',
                        'md5OfBody' => \md5($encoded['body']),
                        'messageAttributes' => [
                            'X-Symfony-Messenger' => [
                                'dataType' => 'String',
                                'stringValue' => \json_encode($encoded['headers'] ?? [], \JSON_THROW_ON_ERROR),
                            ],
                        ],
                        'messageId' => 'message-id',
                        'receiptHandle' => 'receipt-message-id',
                    ],
                ],
            ]),
            new Context(
                'aws-request-id',
                (int)(\microtime(true) * 1000) + 60000,
                'arn:aws:lambda:ap-southeast-2:123456789012:function:test',
                ''
            )
        );

        self::assertInstanceOf(WorkerMessageReceivedEvent::class, $receivedEvent);
        self::assertSame('async', $receivedEvent->getReceiverName());
        self::assertInstanceOf(stdClass::class, $receivedEvent->getEnvelope()->getMessage());
        self::assertInstanceOf(WorkerMessageHandledEvent::class, $handledEvent);
    }
}
