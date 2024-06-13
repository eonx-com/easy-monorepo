<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine\Checker;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyAsync\Doctrine\Exception\DoctrineConnectionNotOkException;
use EonX\EasyAsync\Doctrine\Exception\DoctrineManagerClosedException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class ManagersSanityChecker
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @param string[]|null $managers
     *
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineManagerClosedException
     */
    public function checkSanity(?array $managers = null): void
    {
        // If no managers given, default to all
        $managers ??= \array_keys($this->registry->getManagerNames());

        foreach ($managers as $managerName) {
            $manager = $this->registry->getManager($managerName);

            if ($manager instanceof EntityManagerInterface) {
                $this->checkEntityManager($manager, $managerName);

                continue;
            }

            $this->logger->warning(\sprintf(
                'Type "%s" for manager "%s" not supported by sanity checker',
                $manager::class,
                $managerName
            ));
        }
    }

    /**
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exception\DoctrineManagerClosedException
     */
    private function checkEntityManager(EntityManagerInterface $entityManager, string $name): void
    {
        // Check if closed
        if ($entityManager->isOpen() === false) {
            throw new DoctrineManagerClosedException(\sprintf('Manager "%s" closed', $name));
        }

        $conn = $entityManager->getConnection();

        // No need to check connection if not connected
        if ($conn->isConnected() === false) {
            return;
        }

        // Ensure connection is using replica before each message because if app is setting
        // keepReplica: true, the connection will stay connected to the last one used which could be the primary
        // In most cases, applications will first read data from the database before writing, so it makes sense
        // to ensure it uses replica
        if ($conn instanceof PrimaryReadReplicaConnection) {
            $conn->ensureConnectedToReplica();
        }

        // Check connection ok
        try {
            $conn->fetchAllAssociative($conn->getDatabasePlatform()->getDummySelectSQL());
        } catch (Throwable $throwable) {
            throw new DoctrineConnectionNotOkException(
                \sprintf('Connection for manager "%s" not ok: %s', $name, $throwable->getMessage()),
                \is_string($throwable->getCode()) ? 0 : $throwable->getCode(),
                $throwable
            );
        }
    }
}
