<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Events;

final class EntityUpdatedEvent
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
