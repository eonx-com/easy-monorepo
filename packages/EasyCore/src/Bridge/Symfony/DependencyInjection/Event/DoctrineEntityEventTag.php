<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Event;

use EonX\EasyCore\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface;

final class DoctrineEntityEventTag implements EventTagInterface
{
    /**
     * @var null|string
     */
    private $entity;

    /**
     * @var null|string
     */
    private $entityManager;

    /**
     * @var string
     */
    private $event;

    /**
     * @var null|bool
     */
    private $lazy;

    /**
     * @var null|string
     */
    private $method;

    public function __construct(
        string $event,
        string $entity,
        ?string $method = null,
        ?string $entityManager = null,
        ?bool $lazy = null
    ) {
        $this->event = $event;
        $this->entity = $entity;
        $this->method = $method;
        $this->entityManager = $entityManager;
        $this->lazy = $lazy;
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        $attr = [
            'event' => $this->event,
            'entity' => $this->entity,
        ];

        if ($this->entityManager) {
            $attr['entity_manager'] = $this->entityManager;
        }

        if ($this->method) {
            $attr['method'] = $this->method;
        }

        if ($this->lazy) {
            $attr['lazy'] = 'true';
        }

        return $attr;
    }

    public function getName(): string
    {
        return 'doctrine.orm.entity_listener';
    }
}
