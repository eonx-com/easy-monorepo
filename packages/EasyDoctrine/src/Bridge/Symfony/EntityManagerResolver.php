<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony;

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

    public function getManager(): EntityManagerInterface
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $this->managerRegistry->getManager();

        return $entityManager;
    }
}
