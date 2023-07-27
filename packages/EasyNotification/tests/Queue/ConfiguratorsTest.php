<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Queue;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;
use EonX\EasyNotification\Messages\RealTimeMessage;
use EonX\EasyNotification\Messages\SlackMessage;
use EonX\EasyNotification\Queue\Configurators\ProviderHeaderConfigurator;
use EonX\EasyNotification\Queue\Configurators\QueueUrlConfigurator;
use EonX\EasyNotification\Queue\Configurators\RealTimeBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\SignatureConfigurator;
use EonX\EasyNotification\Queue\Configurators\SlackBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\TypeConfigurator;
use EonX\EasyNotification\Queue\QueueMessage;
use EonX\EasyNotification\Tests\AbstractTestCase;
use EonX\EasyNotification\Tests\Stubs\MessageStub;
use Error;
use PHPUnit\Framework\TestCase;

final class ConfiguratorsTest extends AbstractTestCase
{
    /**
     * @see testConfigure
     */
    public static function providerTestConfigure(): iterable
    {
        yield 'Provider Header' => [
            new ProviderHeaderConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertEquals(
                    [
                        QueueMessageInterface::HEADER_PROVIDER => static::$defaultConfig['externalId'],
                    ],
                    $queueMessage->getHeaders()
                );
            },
        ];

        yield 'Queue URL' => [
            new QueueUrlConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertEquals(static::$defaultConfig['queueUrl'], $queueMessage->getQueueUrl());
            },
        ];

        yield 'RealTimeBody with not RealTime message' => [
            new RealTimeBodyConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'RealTimeBody' => [
            new RealTimeBodyConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([
                'name' => 'nathan',
            ], ['nathan']),
            static function (QueueMessageInterface $queueMessage): void {
                $expected = '{"body":"{\"name\":\"nathan\"}","topics":["nathan"]}';

                self::assertEquals($expected, $queueMessage->getBody());
            },
        ];

        yield 'Signature with empty body' => [
            new SignatureConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertFalse(isset($queueMessage->getHeaders()[QueueMessageInterface::HEADER_SIGNATURE]));
            },
            (new QueueMessage())->setBody(''),
        ];

        yield 'Signature' => [
            new SignatureConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                $hash = \hash_hmac(static::$defaultConfig['algorithm'], 'my-body', static::$defaultConfig['secret']);
                $signature = $queueMessage->getHeaders()[QueueMessageInterface::HEADER_SIGNATURE];

                self::assertTrue(\hash_equals($hash, $signature));
            },
            (new QueueMessage())->setBody('my-body'),
        ];

        yield 'Type with RealTimeMessage' => [
            new TypeConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertEquals(
                    RealTimeMessage::TYPE_REAL_TIME,
                    $queueMessage->getHeaders()[QueueMessageInterface::HEADER_TYPE]
                );
            },
            (new QueueMessage())->setBody('my-body'),
        ];

        yield 'SlackBody with not Slack message' => [
            new SlackBodyConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'SlackBody' => [
            new SlackBodyConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new SlackMessage('channel', 'text'),
            static function (QueueMessageInterface $queueMessage): void {
                $expected = '{"channel":"channel","text":"text"}';

                self::assertEquals($expected, $queueMessage->getBody());
            },
        ];
    }

    /**
     * @dataProvider providerTestConfigure
     */
    public function testConfigure(
        QueueMessageConfiguratorInterface $configurator,
        ConfigInterface $config,
        MessageInterface $message,
        callable $test,
        ?QueueMessageInterface $queueMessage = null,
    ): void {
        $test($configurator->configure($config, $queueMessage ?? new QueueMessage(), $message), $this);
    }
}
