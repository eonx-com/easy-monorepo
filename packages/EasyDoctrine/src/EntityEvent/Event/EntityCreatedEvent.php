<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

/**
 * @template T of object
 *
 * @implements \EonX\EasyDoctrine\EntityEvent\Event\EntityActionEventInterface<T>
 */
final readonly class EntityCreatedEvent implements EntityActionEventInterface
{
    use EntityEventNameTrait;

    /**
     * @param T $entity
     */
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

    /**
     * @return T
     */
    public function getEntity(): object
    {
        return $this->entity;
    }
}
