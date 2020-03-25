<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Event;

use EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface;

final class KernelEventTag implements EventTagInterface
{
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

    public function __construct(?int $priority = null, ?string $event = null, ?string $method = null)
    {
        $this->priority = $priority;
        $this->event = $event;
        $this->method = $method;
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

        return $attr;
    }

    public function getName(): string
    {
        return 'kernel.event_listener';
    }
}
