<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\Type;
use EonX\EasyNotification\Exception\InvalidRealTimeMessageTypeException;

final class RealTimeMessage extends AbstractMessage
{
    private const REAL_TIME_TYPES = [Type::Flash, Type::RealTime];

    /**
     * @var string[]
     */
    private array $topics;

    private Type $type;

    /**
     * @param string[]|null $topics
     */
    public function __construct(?array $body = null, ?array $topics = null, ?Type $type = null)
    {
        $this->type($type ?? Type::RealTime);
        $this->topics = $topics ?? [];

        parent::__construct($body);
    }

    /**
     * @param string[]|null $topics
     */
    public static function create(?array $body = null, ?array $topics = null, ?Type $type = null): self
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

    public function getType(): Type
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

    public function type(Type $type): self
    {
        if (\in_array($type, self::REAL_TIME_TYPES, true) === false) {
            throw new InvalidRealTimeMessageTypeException(\sprintf(
                'Given type "%s" invalid. Valid types: ["%s"]',
                $type->name,
                \implode(
                    '", "',
                    \array_map(static fn (Type $type): string => $type->name, self::REAL_TIME_TYPES)
                )
            ));
        }

        $this->type = $type;

        return $this;
    }
}
