<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

use ReflectionClass;

/**
 * @template T of object
 */
abstract readonly class AbstractEntityEvent
{
    /**
     * @param T $entity
     */
    public function __construct(
        protected object $entity,
        protected array $changeSet,
    ) {}

    /**
     * @param class-string $entityClass
     */
    public static function buildEventName(string $entityClass): string
    {
        $shortName = new ReflectionClass(static::class)->getShortName();
        $action = \strtolower(\str_replace(['Entity', 'Event'], '', $shortName));

        return \sprintf('entity.%s.%s', $action, $entityClass);
    }

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

    public function getEventName(): string
    {
        return static::buildEventName($this->entity::class);
    }
}
