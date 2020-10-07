<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class EntityManagerResolver
{
    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getManager(): EntityManagerInterface
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $this->managerRegistry->getManager();

        return $entityManager;
    }
}
