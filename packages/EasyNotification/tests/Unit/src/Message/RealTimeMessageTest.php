<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Message;

use EonX\EasyNotification\Enum\MessageType;
use EonX\EasyNotification\Exception\InvalidRealTimeMessageTypeException;
use EonX\EasyNotification\Message\RealTimeMessage;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;

final class RealTimeMessageTest extends AbstractUnitTestCase
{
    protected static array $body = [
        'message' => 'hey there',
    ];

    /**
     * @var string[]
     */
    protected static array $topics = ['nathan', 'pavel'];

    /**
     * @see testGetters
     */
    public static function provideGettersData(): iterable
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
            fn (): RealTimeMessage => RealTimeMessage::create(static::$body)->setTopics(static::$topics),
            static::$body,
            static::$topics,
        ];

        yield 'Create method + body + topics' => [
            function (): RealTimeMessage {
                $message = RealTimeMessage::create()->setTopics(static::$topics);
                $message->setBody(static::$body);

                return $message;
            },
            static::$body,
            static::$topics,
        ];
    }

    /**
     * @param string[] $topics
     *
     * @throws \Nette\Utils\JsonException
     */
    #[DataProvider('provideGettersData')]
    public function testGetters(callable $getMessage, array $body, array $topics): void
    {
        // Trick for coverage
        /** @var \EonX\EasyNotification\Message\RealTimeMessage $message */
        $message = $getMessage();

        self::assertSame(MessageType::RealTime, $message->getType());
        self::assertSame(Json::encode($body), $message->getBody());
        self::assertSame($topics, $message->getTopics());
    }

    public function testInvalidTypeException(): void
    {
        $this->expectException(InvalidRealTimeMessageTypeException::class);

        RealTimeMessage::create(null, null, MessageType::Slack);
    }

    public function testMessageCanHaveFlashType(): void
    {
        $message = RealTimeMessage::create(null, null, MessageType::Flash);

        self::assertEquals(MessageType::Flash, $message->getType());
    }
}
