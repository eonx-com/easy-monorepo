<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

final class EntityUpdatedEvent implements EntityActionEventInterface
{
    public function __construct(
        private object $entity,
        private array $changeSet,
    ) {
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
