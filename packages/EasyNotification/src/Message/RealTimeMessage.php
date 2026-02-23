<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\MessageType;
use EonX\EasyNotification\Exception\InvalidRealTimeMessageTypeException;

final class RealTimeMessage extends AbstractMessage
{
    private const array REAL_TIME_TYPES = [MessageType::Flash, MessageType::RealTime];

    /**
     * @var string[]
     */
    private array $topics;

    private MessageType $type;

    /**
     * @param string[]|null $topics
     */
    public function __construct(?array $body = null, ?array $topics = null, ?MessageType $type = null)
    {
        $this->setType($type ?? MessageType::RealTime);
        $this->topics = $topics ?? [];

        parent::__construct($body);
    }

    /**
     * @param string[]|null $topics
     */
    public static function create(?array $body = null, ?array $topics = null, ?MessageType $type = null): self
    {
        return new self($body, $topics, $type);
    }

    /**
     * @return string[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getType(): MessageType
    {
        return $this->type;
    }

    /**
     * @param string[] $topics
     */
    public function setTopics(array $topics): self
    {
        $this->topics = $topics;

        return $this;
    }

    public function setType(MessageType $type): self
    {
        if (\in_array($type, self::REAL_TIME_TYPES, true) === false) {
            throw new InvalidRealTimeMessageTypeException(\sprintf(
                'Given type "%s" invalid. Valid types: ["%s"]',
                $type->name,
                \implode(
                    '", "',
                    \array_map(static fn (MessageType $type): string => $type->name, self::REAL_TIME_TYPES)
                )
            ));
        }

        $this->type = $type;

        return $this;
    }
}
