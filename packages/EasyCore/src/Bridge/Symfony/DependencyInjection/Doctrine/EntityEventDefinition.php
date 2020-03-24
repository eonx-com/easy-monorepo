<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Doctrine;

final class EntityEventDefinition
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
        ?string $method = null,
        ?string $entity = null,
        ?string $entityManager = null,
        ?bool $lazy = null
    ) {
        $this->event = $event;
        $this->method = $method;
        $this->entity = $entity;
        $this->entityManager = $entityManager;
        $this->lazy = $lazy;
    }

    public function getArguments(string $defaultEntity): array
    {
        $arguments = ['event' => $this->event, 'entity' => $this->entity ?? $defaultEntity];

        if ($this->entityManager) {
            $arguments['entity_manager'] = $this->entityManager;
        }

        if ($this->method) {
            $arguments['method'] = $this->method;
        }

        if ($this->lazy) {
            $arguments['lazy'] = 'true';
        }

        return $arguments;
    }
}
