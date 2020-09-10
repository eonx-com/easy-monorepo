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
    protected static $body = [
        'option' => 'value',
        'channel' => 'channel',
        'text' => 'text',
    ];

    /**
     * @return iterable<mixed>
     */
    public function providerTestGetters(): iterable
    {
        yield 'Constructor' => [
            function (): SlackMessage {
                return new SlackMessage('channel', 'text', ['option' => 'value']);
            },
            static::$body,
        ];

        yield 'Create method' => [
            function (): SlackMessage {
                return SlackMessage::create('channel', 'text', ['option' => 'value']);
            },
            static::$body,
        ];

        yield 'Create method + text + body' => [
            function (): SlackMessage {
                $message = SlackMessage::create('channel')->text('text');
                $message->body(['option' => 'value']);

                return $message;
            },
            static::$body,
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
    public function testGetters(callable $getMessage, array $body): void
    {
        /** @var \EonX\EasyNotification\Messages\SlackMessage $message */
        // Trick for coverage
        $message = $getMessage();

        self::assertEquals(MessageInterface::TYPE_SLACK, $message->getType());
        self::assertEquals(Json::encode($body), $message->getBody());
    }
}
