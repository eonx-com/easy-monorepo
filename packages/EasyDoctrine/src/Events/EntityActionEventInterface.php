<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

interface EntityActionEventInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getChangeSet(): array;

    public function getEntity(): object;
}
