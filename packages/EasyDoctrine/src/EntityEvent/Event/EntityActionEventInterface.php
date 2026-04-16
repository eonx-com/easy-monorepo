<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

/**
 * @template T of object
 */
interface EntityActionEventInterface
{
    public function getChangeSet(): array;

    /**
     * @return T
     */
    public function getEntity(): object;

    public function getEventName(): string;
}
