<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Event;

use EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface;

final class KernelEventTag implements EventTagInterface
{
    /**
     * @var null|string
     */
    private $dispatcher;

    /**
     * @var null|string
     */
    private $event;

    /**
     * @var null|string
     */
    private $method;

    /**
     * @var null|int
     */
    private $priority;

    public function __construct(
        ?string $event = null,
        ?string $method = null,
        ?int $priority = null,
        ?string $dispatcher = null
    ) {
        $this->priority = $priority;
        $this->event = $event;
        $this->method = $method;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        $attr = [];

        if ($this->event) {
            $attr['event'] = $this->event;
        }

        if ($this->method) {
            $attr['method'] = $this->method;
        }

        if ($this->priority) {
            $attr['priority'] = $this->priority;
        }

        if ($this->dispatcher) {
            $attr['dispatcher'] = $this->dispatcher;
        }

        return $attr;
    }

    public function getName(): string
    {
        return 'kernel.event_listener';
    }
}
