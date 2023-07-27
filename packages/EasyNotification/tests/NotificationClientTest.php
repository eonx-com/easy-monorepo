<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Exceptions\ApiRequestFailedException;
use EonX\EasyNotification\Exceptions\ConfigRequiredException;
use EonX\EasyNotification\Exceptions\InvalidRealTimeMessageStatusException;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\Messages\RealTimeMessage;
use EonX\EasyNotification\NotificationClient;
use EonX\EasyNotification\Queue\Configurators\ProviderHeaderConfigurator;
use EonX\EasyNotification\Queue\Configurators\QueueUrlConfigurator;
use EonX\EasyNotification\Queue\Configurators\RealTimeBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\TypeConfigurator;
use EonX\EasyNotification\Tests\Bridge\Symfony\Stubs\SqsQueueTransportFactoryStub;
use EonX\EasyNotification\Tests\Stubs\HttpClientStub;
use EonX\EasyNotification\Tests\Stubs\SqsClientStub;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NotificationClientTest extends AbstractTestCase
{
    public function testApiRequestException(): void
    {
        $this->expectException(ApiRequestFailedException::class);

        $config = Config::fromArray(static::$defaultConfig);
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
        $config = Config::fromArray(static::$defaultConfig);
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

        self::assertEquals('DELETE', $httpClientStub->getMethod());
        self::assertEquals($config->getApiUrl() . 'messages/message-id', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    public function testGetMessages(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
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

        self::assertEquals('GET', $httpClientStub->getMethod());
        self::assertEquals($config->getApiUrl() . 'messages', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    public function testSend(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
        $sqsClientStub = new SqsClientStub();
        $client = $this->getNotificationClient($sqsClientStub);

        $client->withConfig($config)
            ->send(RealTimeMessage::create([
                'name' => 'nathan',
            ], ['topic']));

        $expected = [
            'QueueUrl' => static::$defaultConfig['queueUrl'],
            'MessageAttributes' => [
                'provider' => [
                    'DataType' => 'String',
                    'StringValue' => static::$defaultConfig['externalId'],
                ],
                'type' => [
                    'DataType' => 'String',
                    'StringValue' => RealTimeMessage::TYPE_REAL_TIME,
                ],
            ],
            'MessageBody' => '{"body":"{\"name\":\"nathan\"}","topics":["topic"]}',
        ];

        self::assertEquals($expected, $sqsClientStub->getCalls()[0]);
    }

    public function testUpdateMessagesStatusInvalidStatusException(): void
    {
        $this->expectException(InvalidRealTimeMessageStatusException::class);

        $config = Config::fromArray(static::$defaultConfig);

        $this->getNotificationClient()
            ->withConfig($config)
            ->updateMessagesStatus(['message'], 'invalid');
    }

    public function testUpdateMessagesStatusSuccessful(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
        $httpClientStub = new HttpClientStub();
        $client = $this->getNotificationClient(null, $httpClientStub)
            ->withConfig($config);

        $client->updateMessagesStatus(['message-id'], RealTimeMessage::STATUS_READ);

        $expectedOptions = [
            'json' => [
                'messages' => ['message-id'],
                'status' => RealTimeMessage::STATUS_READ,
            ],
            'auth_basic' => [$config->getApiKey()],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        self::assertEquals('PUT', $httpClientStub->getMethod());
        self::assertEquals($config->getApiUrl() . 'messages', $httpClientStub->getUrl());
        self::assertEquals($expectedOptions, $httpClientStub->getOptions());
    }

    /**
     * @return iterable<\EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface>
     */
    private function getConfigurators(): iterable
    {
        yield new RealTimeBodyConfigurator();
        yield new TypeConfigurator();
        yield new ProviderHeaderConfigurator();
        yield new QueueUrlConfigurator();
    }

    private function getNotificationClient(
        ?SqsClientStub $sqsClientStub = null,
        ?HttpClientInterface $httpClient = null,
    ): NotificationClientInterface {
        $transportFactory = new SqsQueueTransportFactoryStub($sqsClientStub ?? new SqsClientStub());

        return new NotificationClient($this->getConfigurators(), $transportFactory, $httpClient);
    }
}
