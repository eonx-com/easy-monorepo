<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\MessageType;

final class SlackMessage extends AbstractMessage
{
    public function __construct(
        private string $channel,
        private ?string $text = null,
        ?array $body = null,
    ) {
        parent::__construct($body);
    }

    public static function create(string $channel, ?string $text = null, ?array $body = null): self
    {
        return new self($channel, $text, $body);
    }

    public function getBody(): string
    {
        $extra = [
            'channel' => $this->channel,
        ];

        if ($this->text !== null) {
            $extra['text'] = $this->text;
        }

        $this->mergeBody($extra);

        return parent::getBody();
    }

    public function getType(): MessageType
    {
        return MessageType::Slack;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
