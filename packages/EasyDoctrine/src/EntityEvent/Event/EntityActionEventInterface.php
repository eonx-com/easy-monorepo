<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

interface EntityActionEventInterface
{
    public function getChangeSet(): array;

    public function getEntity(): object;
}
