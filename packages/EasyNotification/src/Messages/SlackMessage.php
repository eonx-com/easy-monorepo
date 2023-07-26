<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class SlackMessage extends AbstractMessage
{
    /**
     * @param mixed[]|null $body
     */
    public function __construct(
        private string $channel,
        private ?string $text = null,
        ?array $body = null,
    ) {
        parent::__construct($body);
    }

    /**
     * @param mixed[]|null $body
     */
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

    public function getType(): string
    {
        return self::TYPE_SLACK;
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
