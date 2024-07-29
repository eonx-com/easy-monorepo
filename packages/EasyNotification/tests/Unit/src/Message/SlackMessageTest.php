<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Message;

use EonX\EasyNotification\Enum\MessageType;
use EonX\EasyNotification\Message\SlackMessage;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;

final class SlackMessageTest extends AbstractUnitTestCase
{
    /**
     * @var string[]
     */
    protected static array $body = [
        'option' => 'value',
        'channel' => 'channel',
        'text' => 'text',
    ];

    /**
     * @see testGetters
     */
    public static function provideGettersData(): iterable
    {
        yield 'Constructor' => [
            static fn (): SlackMessage => new SlackMessage('channel', 'text', [
                'option' => 'value',
            ]),
            static::$body,
        ];

        yield 'Create method' => [
            static fn (): SlackMessage => SlackMessage::create('channel', 'text', [
                'option' => 'value',
            ]),
            static::$body,
        ];

        yield 'Create method + text + body' => [
            static function (): SlackMessage {
                $message = SlackMessage::create('channel')->setText('text');
                $message->setBody([
                    'option' => 'value',
                ]);

                return $message;
            },
            static::$body,
        ];
    }

    /**
     * @throws \Nette\Utils\JsonException
     */
    #[DataProvider('provideGettersData')]
    public function testGetters(callable $getMessage, array $body): void
    {
        // Trick for coverage
        /** @var \EonX\EasyNotification\Message\SlackMessage $message */
        $message = $getMessage();

        self::assertSame(MessageType::Slack, $message->getType());
        self::assertSame(Json::encode($body), $message->getBody());
    }
}
