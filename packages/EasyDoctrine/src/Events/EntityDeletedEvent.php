<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

final class EntityDeletedEvent
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
     * @return array<string, mixed>
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
