<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Messages;

use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Messages\SlackMessage;
use EonX\EasyNotification\Tests\AbstractTestCase;
use Nette\Utils\Json;

final class SlackMessageTest extends AbstractTestCase
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
    public static function providerTestGetters(): iterable
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
                $message = SlackMessage::create('channel')->text('text');
                $message->body([
                    'option' => 'value',
                ]);

                return $message;
            },
            static::$body,
        ];
    }

    /**
     * @throws \Nette\Utils\JsonException
     *
     * @dataProvider providerTestGetters
     */
    public function testGetters(callable $getMessage, array $body): void
    {
        // Trick for coverage
        /** @var \EonX\EasyNotification\Messages\SlackMessage $message */
        $message = $getMessage();

        self::assertSame(MessageInterface::TYPE_SLACK, $message->getType());
        self::assertSame(Json::encode($body), $message->getBody());
    }
}
