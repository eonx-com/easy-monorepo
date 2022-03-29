<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

/**
 * @deprecated Use \EonX\EasyDoctrine\Events\DeferredEntityCreatedEvent
 */
final class EntityCreatedEvent implements EntityActionEventInterface
{
    /**
     * @var array<string, mixed>
     */
    private $changeSet;

    /**
     * @var object
     */
    private $entity;

    /**
     * @param object $entity
     * @param array<string, mixed> $changeSet
     */
    public function __construct(object $entity, array $changeSet)
    {
        $this->entity = $entity;
        $this->changeSet = $changeSet;
    }

    /**
     * @inheritdoc
     */
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
