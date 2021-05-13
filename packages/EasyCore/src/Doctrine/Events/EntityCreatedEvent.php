<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Events;

use EonX\EasyCore\Interfaces\DatabaseEntityInterface;

final class EntityCreatedEvent
{
    /**
     * @var \EonX\EasyCore\Interfaces\DatabaseEntityInterface
     */
    private $entity;

    public function __construct(DatabaseEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): DatabaseEntityInterface
    {
        return $this->entity;
    }
}
