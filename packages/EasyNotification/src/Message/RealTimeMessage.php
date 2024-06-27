<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Exception\InvalidRealTimeMessageTypeException;

final class RealTimeMessage extends AbstractMessage
{
    public const REAL_TIME_TYPES = [self::TYPE_FLASH, self::TYPE_REAL_TIME];

    public const STATUSES = [self::STATUS_ON_FLY, self::STATUS_READ, self::STATUS_RECEIVED];

    public const STATUS_ON_FLY = 'on_fly';

    public const STATUS_READ = 'read';

    public const STATUS_RECEIVED = 'received';

    /**
     * @var string[]
     */
    private array $topics;

    private string $type;

    /**
     * @param string[]|null $topics
     */
    public function __construct(?array $body = null, ?array $topics = null, ?string $type = null)
    {
        $this->type($type ?? self::TYPE_REAL_TIME);
        $this->topics = $topics ?? [];

        parent::__construct($body);
    }

    /**
     * @param string[]|null $topics
     */
    public static function create(?array $body = null, ?array $topics = null, ?string $type = null): self
    {
        return new self($body, $topics, $type);
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
        return $this->type;
    }

    /**
     * @param string[] $topics
     */
    public function topics(array $topics): self
    {
        $this->topics = $topics;

        return $this;
    }

    public function type(string $type): self
    {
        if (\in_array($type, self::REAL_TIME_TYPES, true) === false) {
            throw new InvalidRealTimeMessageTypeException(\sprintf(
                'Given type "%s" invalid. Valid types: ["%s"]',
                $type,
                \implode('", "', self::REAL_TIME_TYPES)
            ));
        }

        $this->type = $type;

        return $this;
    }
}
