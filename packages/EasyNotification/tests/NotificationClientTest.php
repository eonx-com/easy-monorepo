<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Messages\RealTimeMessage;
use EonX\EasyNotification\NotificationClient;
use EonX\EasyNotification\Queue\Configurators\ProviderHeaderConfigurator;
use EonX\EasyNotification\Queue\Configurators\QueueUrlConfigurator;
use EonX\EasyNotification\Queue\Configurators\RealTimeBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\TypeConfigurator;
use EonX\EasyNotification\Tests\Bridge\Symfony\Stubs\SqsQueueTransportFactoryStub;
use EonX\EasyNotification\Tests\Stubs\SqsClientStub;

final class NotificationClientTest extends AbstractTestCase
{
    public function testSend(): void
    {
        $config = Config::fromArray(static::$defaultConfig);
        $stub = new SqsClientStub();
        $transportFactory = new SqsQueueTransportFactoryStub($stub);
        $client = new NotificationClient($this->getConfigurators(), $transportFactory);

        $client->send($config, RealTimeMessage::create(['name' => 'nathan'], ['topic']));

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

        self::assertEquals($expected, $stub->getCalls()[0]);
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
}
