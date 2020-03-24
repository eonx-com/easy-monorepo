<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine;

final class EventDefinition
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

    public function getArguments(): array
    {
        $arguments = ['event' => $this->event];

        if ($this->priority) {
            $arguments['priority'] = $this->priority;
        }

        if ($this->connection) {
            $arguments['connection'] = $this->connection;
        }

        if ($this->lazy) {
            $arguments['lazy'] = 'true';
        }

        return $arguments;
    }
}
