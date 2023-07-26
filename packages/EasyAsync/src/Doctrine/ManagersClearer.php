<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine;

use Doctrine\Persistence\ManagerRegistry;

final class ManagersClearer
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
    }

    /**
     * @param string[]|null $managers
     */
    public function clear(?array $managers = null): void
    {
        // If no managers given, default to all
        $managers ??= \array_keys($this->registry->getManagerNames());

        foreach ($managers as $managerName) {
            $this->registry->getManager($managerName)
                ->clear();
        }
    }
}
