<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Orm;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\AppStateInitializers\AbstractAppStateInitializer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class ManagerConnectionsInitializer extends AbstractAppStateInitializer
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly LoggerInterface $logger = new NullLogger(),
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function initState(): void
    {
        // This class recycles the db connection if compromised BEFORE processing a request.
        // It should help prevent a request because of lost connection
        foreach ($this->managerRegistry->getManagers() as $manager) {
            if ($manager instanceof EntityManagerInterface) {
                $conn = $manager->getConnection();

                // If connection is not connected, nothing to do
                if ($conn->isConnected() === false) {
                    continue;
                }

                // Ensure connection is using replica before each request because if app is setting
                // keepReplica: true, the connection will stay connected to the last one used which could be the primary
                // In most cases, applications will first read data from the database before writing, so it makes sense
                // to ensure it uses replica
                if ($conn instanceof PrimaryReadReplicaConnection) {
                    $conn->ensureConnectedToReplica();
                }

                try {
                    $conn->fetchAllAssociative($conn->getDatabasePlatform()->getDummySelectSQL());
                } catch (Throwable $throwable) {
                    $this->logger->debug(\sprintf(
                        'Close DB Connection because compromised: %s',
                        $throwable->getMessage()
                    ));

                    // If connection is compromised, simply close it, so it can be re-opened
                    $conn->close();
                }
            }
        }
    }
}
