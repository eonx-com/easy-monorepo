<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Messages;

use EonX\EasyNotification\Exceptions\InvalidRealTimeMessageTypeException;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Messages\RealTimeMessage;
use EonX\EasyNotification\Tests\AbstractTestCase;
use Nette\Utils\Json;

final class RealTimeMessageTest extends AbstractTestCase
{
    /**
     * @var mixed[]
     */
    protected static array $body = [
        'message' => 'hey there',
    ];

    /**
     * @var string[]
     */
    protected static array $topics = ['nathan', 'pavel'];

    /**
     * @return iterable<mixed>
     *
     * @see testGetters
     */
    public static function providerTestGetters(): iterable
    {
        yield 'Constructor' => [
            fn (): RealTimeMessage => new RealTimeMessage(static::$body, static::$topics),
            static::$body,
            static::$topics,
        ];

        yield 'Create method' => [
            fn (): RealTimeMessage => RealTimeMessage::create(static::$body, static::$topics),
            static::$body,
            static::$topics,
        ];

        yield 'Create method + topics' => [
            fn (): RealTimeMessage => RealTimeMessage::create(static::$body)->topics(static::$topics),
            static::$body,
            static::$topics,
        ];

        yield 'Create method + body + topics' => [
            function (): RealTimeMessage {
                $message = RealTimeMessage::create()->topics(static::$topics);
                $message->body(static::$body);

                return $message;
            },
            static::$body,
            static::$topics,
        ];
    }

    /**
     * @param mixed[] $body
     * @param string[] $topics
     *
     * @dataProvider providerTestGetters
     *
     * @throws \Nette\Utils\JsonException
     */
    public function testGetters(callable $getMessage, array $body, array $topics): void
    {
        /** @var \EonX\EasyNotification\Messages\RealTimeMessage $message */
        // Trick for coverage
        $message = $getMessage();

        self::assertEquals(MessageInterface::TYPE_REAL_TIME, $message->getType());
        self::assertEquals(Json::encode($body), $message->getBody());
        self::assertEquals($topics, $message->getTopics());
    }

    public function testInvalidTypeException(): void
    {
        $this->expectException(InvalidRealTimeMessageTypeException::class);

        RealTimeMessage::create(null, null, 'invalid');
    }

    public function testMessageCanHaveFlashType(): void
    {
        $message = RealTimeMessage::create(null, null, MessageInterface::TYPE_FLASH);

        self::assertEquals(MessageInterface::TYPE_FLASH, $message->getType());
    }
}
