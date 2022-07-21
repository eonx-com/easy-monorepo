<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\AppStateCheckers\AbstractAppStateChecker;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ManagersChecker extends AbstractAppStateChecker
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly LoggerInterface $logger = new NullLogger(),
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function isApplicationStateCompromised(): bool
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            if ($manager instanceof EntityManagerInterface && $this->isEntityManagerCompromised($manager)) {
                return true;
            }
        }

        return false;
    }

    private function isEntityManagerCompromised(EntityManagerInterface $entityManager): bool
    {
        if ($entityManager->isOpen() === false || $entityManager->getConnection()->isConnected() === false) {
            return true;
        }

        try {
            $conn = $entityManager->getConnection();
            $conn->fetchAllAssociative($conn->getDatabasePlatform()->getDummySelectSQL());

            return false;
        } catch (\Throwable $throwable) {
            $this->logger->debug(\sprintf('DB Connection compromised: %s', $throwable->getMessage()));

            return true;
        }
    }
}
