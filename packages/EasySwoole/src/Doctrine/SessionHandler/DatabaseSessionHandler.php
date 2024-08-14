<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\SessionHandler;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\ORM\EntityManagerInterface;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface as SessionUpdateTimestampHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Contracts\Service\ResetInterface;

final class DatabaseSessionHandler implements ResetInterface, SessionHandlerInterface, SessionUpdateTimestampHandler
{
    private ?PdoSessionHandler $decorated = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
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

    public function read(string $id): string|false
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

        $connection = $this->entityManager->getConnection();

        // The PdoSessionHandler is executing raw SQL queries,
        // so we need to ensure we are using the primary connection
        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToPrimary();
        }

        $nativeConnGetter = \method_exists($connection, 'getNativeConnection')
            ? 'getNativeConnection'
            : 'getWrappedConnection';

        $this->decorated = new PdoSessionHandler($connection->{$nativeConnGetter}(), $this->options ?? []);

        // Restore replica connection once PdoSessionHandler instantiated for the rest of the app
        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToReplica();
        }

        return $this->decorated;
    }
}
