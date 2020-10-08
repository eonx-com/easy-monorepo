<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Event;

use EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface;

final class DoctrineEventTag implements EventTagInterface
{
    /**
     * @var null|string
     */
    private $connection;

    /**
     * @var string
     */
    private $event;

    /**
     * @var null|bool
     */
    private $lazy;

    /**
     * @var null|int
     */
    private $priority;

    public function __construct(string $event, ?int $priority = null, ?string $connection = null, ?bool $lazy = null)
    {
        $this->event = $event;
        $this->priority = $priority;
        $this->connection = $connection;
        $this->lazy = $lazy;
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        $attr = [
            'event' => $this->event,
        ];

        if ($this->priority) {
            $attr['priority'] = $this->priority;
        }

        if ($this->connection) {
            $attr['connection'] = $this->connection;
        }

        if ($this->lazy) {
            $attr['lazy'] = 'true';
        }

        return $attr;
    }

    public function getName(): string
    {
        return 'doctrine.event_listener';
    }
}
