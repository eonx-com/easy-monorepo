<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Client;

use EonX\EasyNotification\Client\NotificationClient;
use EonX\EasyNotification\Client\NotificationClientInterface;
use EonX\EasyNotification\Configurator\ProviderHeaderQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\QueueUrlQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\RealTimeBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\TypeQueueMessageConfigurator;
use EonX\EasyNotification\Enum\Status;
use EonX\EasyNotification\Enum\Type;
use EonX\EasyNotification\Exception\ApiRequestFailedException;
use EonX\EasyNotification\Exception\ConfigRequiredException;
use EonX\EasyNotification\Message\RealTimeMessage;
use EonX\EasyNotification\Tests\Stub\Factory\SqsQueueTransportFactoryStub;
use EonX\EasyNotification\Tests\Stub\HttpClient\HttpClientStub;
use EonX\EasyNotification\Tests\Stub\Sqs\SqsClientStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\ValueObject\Config;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NotificationClientTest extends AbstractUnitTestCase
{
    public function testApiRequestException(): void
    {
        $this->expectException(ApiRequestFailedException::class);

        $config = Config::fromArray(self::$defaultConfig);
        $httpClient = new MockHttpClient([new MockResponse('invalid-content')]);
        $client = $this->getNotificationClient(null, $httpClient)
            ->withConfig($config);

        $client->deleteMessage('message-id');
    }

    public function testConfigRequiredException(): void
    {
        $this->expectException(ConfigRequiredException::class);

        $this->getNotificationClient()
            ->deleteMessage('messageId');
    }

    public function testDeleteMessage(): void
    {
        $config = Config::fromArray(self::$defaultConfig);
        $httpClientStub = new HttpClientStub();
        $client = $this->getNotificationClient(null, $httpClientStub)
            ->withConfig($config);

        $client->deleteMessage('message-id');

        $expectedOptions = [
            'auth_basic' => [$config->getApiKey()],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        self::assertSame('DELETE', $httpClientStub->getMethod());
        self::assertSame($config->getApiUrl() . 'messages/message-id', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    public function testGetMessages(): void
    {
        $config = Config::fromArray(self::$defaultConfig);
        $httpClientStub = new HttpClientStub();
        $client = $this->getNotificationClient(null, $httpClientStub)
            ->withConfig($config);

        $client->getMessages(['topic'], [
            'query' => [
                'my-query' => 'my-value',
            ],
        ]);

        $expectedOptions = [
            'auth_basic' => [$config->getApiKey()],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => [
                'topic' => ['topic'],
                'my-query' => 'my-value',
            ],
        ];

        self::assertSame('GET', $httpClientStub->getMethod());
        self::assertSame($config->getApiUrl() . 'messages', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    public function testSend(): void
    {
        $config = Config::fromArray(self::$defaultConfig);
        $sqsClientStub = new SqsClientStub();
        $client = $this->getNotificationClient($sqsClientStub);

        $client->withConfig($config)
            ->send(RealTimeMessage::create([
                'name' => 'nathan',
            ], ['topic']));

        $expected = [
            'QueueUrl' => self::$defaultConfig['queueUrl'],
            'MessageAttributes' => [
                'provider' => [
                    'DataType' => 'String',
                    'StringValue' => self::$defaultConfig['externalId'],
                ],
                'type' => [
                    'DataType' => 'String',
                    'StringValue' => Type::RealTime->value,
                ],
            ],
            'MessageBody' => '{"body":"{\"name\":\"nathan\"}","topics":["topic"]}',
        ];

        self::assertEquals($expected, $sqsClientStub->getCalls()[0]);
    }

    public function testUpdateMessagesStatusSuccessful(): void
    {
        $config = Config::fromArray(self::$defaultConfig);
        $httpClientStub = new HttpClientStub();
        $client = $this->getNotificationClient(null, $httpClientStub)
            ->withConfig($config);

        $client->updateMessagesStatus(['message-id'], Status::Read);

        $expectedOptions = [
            'json' => [
                'messages' => ['message-id'],
                'status' => Status::Read->value,
            ],
            'auth_basic' => [$config->getApiKey()],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        self::assertSame('PUT', $httpClientStub->getMethod());
        self::assertSame($config->getApiUrl() . 'messages', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    /**
     * @return iterable<\EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface>
     */
    private function getConfigurators(): iterable
    {
        yield new RealTimeBodyQueueMessageConfigurator();
        yield new TypeQueueMessageConfigurator();
        yield new ProviderHeaderQueueMessageConfigurator();
        yield new QueueUrlQueueMessageConfigurator();
    }

    private function getNotificationClient(
        ?SqsClientStub $sqsClientStub = null,
        ?HttpClientInterface $httpClient = null,
    ): NotificationClientInterface {
        $transportFactory = new SqsQueueTransportFactoryStub($sqsClientStub ?? new SqsClientStub());

        return new NotificationClient($this->getConfigurators(), $transportFactory, $httpClient);
    }
}
