<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

/**
 * Provides event name logic for entity action events.
 *
 * Derives the action keyword from the class name by stripping the `Entity` prefix
 * and `Event` suffix (e.g. `EntityCreatedEvent` → `created`), then builds the
 * event name as `entity.{action}.{entityClass}`.
 */
trait EntityEventNameTrait
{
    /**
     * @param class-string $entityClass
     */
    public static function buildEventName(string $entityClass): string
    {
        $action = \strtolower(\str_replace(['Entity', 'Event'], '', \basename(\str_replace('\\', '/', static::class))));

        return \sprintf('entity.%s.%s', $action, $entityClass);
    }

    public function getEventName(): string
    {
        return static::buildEventName($this->entity::class);
    }
}
