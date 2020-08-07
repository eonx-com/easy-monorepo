<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class RealTimeMessage extends AbstractMessage
{
    /**
     * @var string[]
     */
    private $topics;

    /**
     * @param mixed[] $body
     * @param null|string[] $topics
     */
    public static function create(array $body, ?array $topics = null): RealTimeMessage
    {
        return (new static($body))->topics($topics);
    }

    /**
     * @return string[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getType(): string
    {
        return self::TYPE_REAL_TIME;
    }

    /**
     * @param string[] $topics
     */
    public function topics(array $topics): self
    {
        $this->topics = $topics;

        return $this;
    }
}
