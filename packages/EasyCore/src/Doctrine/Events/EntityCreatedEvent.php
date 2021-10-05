<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Events;

/**
 * @deprecated since 3.5, will be removed in 4.0. Use EasyDoctrine instead.
 */
final class EntityCreatedEvent
{
    /**
     * @var object
     */
    private $entity;

    public function __construct(object $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
