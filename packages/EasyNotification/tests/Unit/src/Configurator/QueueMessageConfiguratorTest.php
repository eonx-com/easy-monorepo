<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Configurator;

use EonX\EasyNotification\Configurator\ProviderHeaderQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\QueueMessageConfiguratorInterface;
use EonX\EasyNotification\Configurator\QueueUrlQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\RealTimeBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\SignatureQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\SlackBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\TypeQueueMessageConfigurator;
use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessage;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\Message\RealTimeMessage;
use EonX\EasyNotification\Message\SlackMessage;
use EonX\EasyNotification\Tests\Stub\Message\MessageStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\ValueObject\Config;
use EonX\EasyNotification\ValueObject\ConfigInterface;
use Error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QueueMessageConfiguratorTest extends AbstractUnitTestCase
{
    /**
     * @see testConfigure
     */
    public static function provideConfigureData(): iterable
    {
        yield 'Provider Header' => [
            new ProviderHeaderQueueMessageConfigurator(),
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
            new QueueUrlQueueMessageConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertEquals(static::$defaultConfig['queueUrl'], $queueMessage->getQueueUrl());
            },
        ];

        yield 'RealTimeBody with not RealTime message' => [
            new RealTimeBodyQueueMessageConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'RealTimeBody' => [
            new RealTimeBodyQueueMessageConfigurator(),
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
            new SignatureQueueMessageConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertFalse(isset($queueMessage->getHeaders()[QueueMessageInterface::HEADER_SIGNATURE]));
            },
            (new QueueMessage())->setBody(''),
        ];

        yield 'Signature' => [
            new SignatureQueueMessageConfigurator(),
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
            new TypeQueueMessageConfigurator(),
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
            new SlackBodyQueueMessageConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'SlackBody' => [
            new SlackBodyQueueMessageConfigurator(),
            Config::fromArray(static::$defaultConfig),
            new SlackMessage('channel', 'text'),
            static function (QueueMessageInterface $queueMessage): void {
                $expected = '{"channel":"channel","text":"text"}';

                self::assertEquals($expected, $queueMessage->getBody());
            },
        ];
    }

    #[DataProvider('provideConfigureData')]
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
