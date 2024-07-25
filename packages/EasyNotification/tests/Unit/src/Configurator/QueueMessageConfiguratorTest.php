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
use EonX\EasyNotification\Enum\Header;
use EonX\EasyNotification\Enum\Type;
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
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertTrue($queueMessage->getHeaders()->contains(Header::Provider));
                self::assertSame(
                    self::$defaultConfig['externalId'],
                    $queueMessage->getHeaders()
                        ->offsetGet(Header::Provider)
                );
            },
        ];

        yield 'Queue URL' => [
            new QueueUrlQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertSame(self::$defaultConfig['queueUrl'], $queueMessage->getQueueUrl());
            },
        ];

        yield 'RealTimeBody with not RealTime message' => [
            new RealTimeBodyQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'RealTimeBody' => [
            new RealTimeBodyQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([
                'name' => 'nathan',
            ], ['nathan']),
            static function (QueueMessageInterface $queueMessage): void {
                $expected = '{"body":"{\"name\":\"nathan\"}","topics":["nathan"]}';

                self::assertSame($expected, $queueMessage->getBody());
            },
        ];

        yield 'Signature with empty body' => [
            new SignatureQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertFalse($queueMessage->getHeaders()->contains(Header::Signature));
            },
            (new QueueMessage())->setBody(''),
        ];

        yield 'Signature' => [
            new SignatureQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                $hash = \hash_hmac(self::$defaultConfig['algorithm'], 'my-body', self::$defaultConfig['secret']);
                $signature = $queueMessage->getHeaders()
                    ->offsetGet(Header::Signature);

                self::assertTrue(\hash_equals($hash, $signature));
            },
            (new QueueMessage())->setBody('my-body'),
        ];

        yield 'Type with RealTimeMessage' => [
            new TypeQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new RealTimeMessage([]),
            static function (QueueMessageInterface $queueMessage): void {
                self::assertSame(
                    Type::RealTime->value,
                    $queueMessage->getHeaders()
                        ->offsetGet(Header::Type)
                );
            },
            (new QueueMessage())->setBody('my-body'),
        ];

        yield 'SlackBody with not Slack message' => [
            new SlackBodyQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new MessageStub([]),
            static function (QueueMessageInterface $queueMessage, TestCase $testCase): void {
                $testCase->expectException(Error::class);
                $queueMessage->getBody();
            },
        ];

        yield 'SlackBody' => [
            new SlackBodyQueueMessageConfigurator(),
            Config::fromArray(self::$defaultConfig),
            new SlackMessage('channel', 'text'),
            static function (QueueMessageInterface $queueMessage): void {
                $expected = '{"channel":"channel","text":"text"}';

                self::assertSame($expected, $queueMessage->getBody());
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
