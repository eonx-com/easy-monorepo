<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

final class EntityCreatedEvent implements EntityActionEventInterface
{
    public function __construct(
        private object $entity,
        private array $changeSet,
    ) {
    }

    /**
     * @inheritDoc
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
