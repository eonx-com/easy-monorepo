<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class RealTimeMessage extends AbstractMessage
{
    /**
     * @var string[]
     */
    public const STATUSES = [
        self::STATUS_ON_FLY,
        self::STATUS_READ,
        self::STATUS_RECEIVED,
    ];

    /**
     * @var string
     */
    public const STATUS_ON_FLY = 'on_fly';

    /**
     * @var string
     */
    public const STATUS_READ = 'read';

    /**
     * @var string
     */
    public const STATUS_RECEIVED = 'received';

    /**
     * @var string[]
     */
    private $topics;

    /**
     * @param null|mixed[] $body
     * @param null|string[] $topics
     */
    public function __construct(?array $body = null, ?array $topics = null)
    {
        if ($topics !== null) {
            $this->topics = $topics;
        }

        parent::__construct($body);
    }

    /**
     * @param null|mixed[] $body
     * @param null|string[] $topics
     */
    public static function create(?array $body = null, ?array $topics = null): self
    {
        return new static($body, $topics);
    }

    public static function isStatusValid(string $status): bool
    {
        return \in_array($status, self::STATUSES, true);
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
