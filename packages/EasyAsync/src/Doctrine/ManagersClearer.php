<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine;

use Doctrine\Persistence\ManagerRegistry;

final class ManagersClearer
{
    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param null|string[] $managers
     *
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException
     */
    public function clear(?array $managers = null): void
    {
        // If no managers given, default to all
        $managers = $managers ?? $this->registry->getManagerNames();

        foreach ($managers as $managerName) {
            $this->registry->getManager($managerName)
                ->clear();
        }
    }
}
