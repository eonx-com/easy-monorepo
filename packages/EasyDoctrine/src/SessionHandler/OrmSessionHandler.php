<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\SessionHandler;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use RuntimeException;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface as SessionUpdateTimestampHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Contracts\Service\ResetInterface;

final class OrmSessionHandler implements ResetInterface, SessionHandlerInterface, SessionUpdateTimestampHandler
{
    private ?PdoSessionHandler $decorated = null;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly ?string $managerName = null,
        private readonly ?array $options = null,
    ) {
    }

    public function close(): bool
    {
        return $this->getDecorated()
            ->close();
    }

    public function destroy(string $id): bool
    {
        return $this->getDecorated()
            ->destroy($id);
    }

    /**
     * @SuppressWarnings("PHPMD.CamelCaseParameterName") It is defined in the interface
     * @SuppressWarnings("PHPMD.CamelCaseVariableName") It is defined in the interface
     * @SuppressWarnings("PHPMD.ShortMethodName") It is defined in the interface
     */
    public function gc(int $max_lifetime): int|false
    {
        return $this->getDecorated()
            ->gc($max_lifetime);
    }

    public function open(string $path, string $name): bool
    {
        return $this->getDecorated()
            ->open($path, $name);
    }

    public function read(string $id): string
    {
        return $this->getDecorated()
            ->read($id);
    }

    public function reset(): void
    {
        $this->decorated = null;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->getDecorated()
            ->updateTimestamp($id, $data);
    }

    public function validateId(string $id): bool
    {
        return $this->getDecorated()
            ->validateId($id);
    }

    public function write(string $id, string $data): bool
    {
        return $this->getDecorated()
            ->write($id, $data);
    }

    private function getDecorated(): PdoSessionHandler
    {
        if ($this->decorated !== null) {
            return $this->decorated;
        }

        $managerName = $this->managerName ?? $this->managerRegistry->getDefaultManagerName();
        $manager = $this->managerRegistry->getManager($managerName);

        if ($manager instanceof EntityManagerInterface === false) {
            throw new RuntimeException(\sprintf(
                'Manager "%s" is not an instance of EntityManagerInterface.',
                $managerName
            ));
        }

        $connection = $manager->getConnection();

        // The PdoSessionHandler is executing raw SQL queries,
        // so we need to ensure we are using the primary connection
        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToPrimary();
        }

        $nativeConnection = $connection->getNativeConnection();

        if ($nativeConnection instanceof PDO === false) {
            throw new RuntimeException(\sprintf(
                'Native connection for manager "%s" is not an instance of \PDO.',
                $managerName
            ));
        }

        $this->decorated = new PdoSessionHandler($nativeConnection, $this->options ?? []);

        // Restore replica connection once PdoSessionHandler instantiated for the rest of the app
        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToReplica();
        }

        return $this->decorated;
    }
}
