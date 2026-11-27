<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Laravel;

use EonX\EasyNotification\Client\NotificationClientInterface;
use EonX\EasyNotification\Enum\MessageHeader;
use EonX\EasyNotification\Factory\QueueTransportFactoryInterface;
use EonX\EasyNotification\Message\PushMessage;
use EonX\EasyNotification\Provider\CachedConfigProvider;
use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\Provider\SubscribeInfoProvider;
use EonX\EasyNotification\Provider\SubscribeInfoProviderInterface;
use EonX\EasyNotification\Tests\Stub\Factory\SqsQueueTransportFactoryStub;
use EonX\EasyNotification\Tests\Stub\SqsClient\SqsClientStub;
use EonX\EasyNotification\ValueObject\Config;

final class EasyNotificationServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApp(static::$defaultConfig);

        self::assertInstanceOf(NotificationClientInterface::class, $app->get(NotificationClientInterface::class));
    }

    public function testSanityProviders(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(CachedConfigProvider::class, $app->get(ConfigProviderInterface::class));
        self::assertInstanceOf(SubscribeInfoProvider::class, $app->get(SubscribeInfoProviderInterface::class));
    }

    public function testSendAddsIdempotencyKeyHeader(): void
    {
        $app = $this->getApp();
        $sqsClientStub = new SqsClientStub();
        $app->instance(QueueTransportFactoryInterface::class, new SqsQueueTransportFactoryStub($sqsClientStub));
        /** @var \EonX\EasyNotification\Client\NotificationClientInterface $client */
        $client = $app->get(NotificationClientInterface::class);

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
