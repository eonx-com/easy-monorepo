<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Message;

use EonX\EasyNotification\Enum\Type;
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

        self::assertSame(Type::RealTime, $message->getType());
        self::assertSame(Json::encode($body), $message->getBody());
        self::assertSame($topics, $message->getTopics());
    }

    public function testInvalidTypeException(): void
    {
        $this->expectException(InvalidRealTimeMessageTypeException::class);

        RealTimeMessage::create(null, null, Type::Slack);
    }

    public function testMessageCanHaveFlashType(): void
    {
        $message = RealTimeMessage::create(null, null, Type::Flash);

        self::assertEquals(Type::Flash, $message->getType());
    }
}
