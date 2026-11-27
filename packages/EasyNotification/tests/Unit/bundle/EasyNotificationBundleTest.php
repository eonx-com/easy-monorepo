<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Bundle;

use EonX\EasyNotification\Client\NotificationClient;
use EonX\EasyNotification\Client\NotificationClientInterface;
use EonX\EasyNotification\Enum\MessageHeader;
use EonX\EasyNotification\Factory\QueueTransportFactoryInterface;
use EonX\EasyNotification\Message\PushMessage;
use EonX\EasyNotification\Tests\Stub\Factory\SqsQueueTransportFactoryStub;
use EonX\EasyNotification\Tests\Stub\SqsClient\SqsClientStub;
use EonX\EasyNotification\ValueObject\Config;

final class EasyNotificationBundleTest extends AbstractSymfonyTestCase
{
    public function testSanityCheck(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default_config.php'])->getContainer();

        self::assertInstanceOf(NotificationClient::class, $container->get(NotificationClientInterface::class));
    }

    public function testSendAddsIdempotencyKeyHeader(): void
    {
        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/default_config.php'])->getContainer();
        $sqsClientStub = new SqsClientStub();
        $container->set(QueueTransportFactoryInterface::class, new SqsQueueTransportFactoryStub($sqsClientStub));
        /** @var \EonX\EasyNotification\Client\NotificationClientInterface $client */
        $client = $container->get(NotificationClientInterface::class);

        $client->withConfig(Config::fromArray(static::$defaultConfig))
            ->send(PushMessage::create('device', 'token')->setIdempotencyKey('my-idempotency-key'));

        $messageAttributes = $sqsClientStub->getCalls()[0]['MessageAttributes'];
        self::assertSame(
            [
                'DataType' => 'String',
                'StringValue' => 'my-idempotency-key',
            ],
            $messageAttributes[MessageHeader::IdempotencyKey->value] ?? null
        );
    }
}
