<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class SlackMessage extends AbstractMessage
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @var null|string
     */
    private $text;

    /**
     * @param null|mixed[] $body
     */
    public function __construct(string $channel, ?string $text = null, ?array $body = null)
    {
        $this->channel = $channel;
        $this->text = $text;

        parent::__construct($body);
    }

    /**
     * @param null|mixed[] $body
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

        if ($this->text) {
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
