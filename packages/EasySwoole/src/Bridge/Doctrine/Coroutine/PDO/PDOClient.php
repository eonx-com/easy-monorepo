<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\DBAL\Driver\PDO\MySQL\Driver as MySQLDriver;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver as PgSQLDriver;
use OpenSwoole\Core\Coroutine\Client\PDOClient as BasePDOClient;
use PDO;
use ReflectionMethod;
use RuntimeException;

final class PDOClient extends BasePDOClient
{
    private const DRIVER_MAPPING = [
        'pdo_mysql' => MySQLDriver::class,
        'pdo_pgsql' => PgSQLDriver::class,
    ];

    private ?float $lastUsedTime = null;

    public function __call(string $name, array $arguments): mixed
    {
        $this->lastUsedTime = \microtime(true);

        // Openswoole package explicitly sets PDO error mode to ERRMODE_SILENT,
        // but other parts of the application expects ERRMODE_EXCEPTION (e.g. PdoSessionHandler).
        // DbalConnection::getNativeConnection() sets ERRMODE_EXCEPTION, so we need to set it back to silent
        $this->__object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $return = parent::__call($name, $arguments);

        // Reset back to ERRMODE_EXCEPTION for services which have a reference on the PDO object
        $this->__object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $return;
    }

    public function getLastUsedTime(): ?float
    {
        return $this->lastUsedTime;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    protected function makeClient(): void
    {
        /** @var \EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO\PDOClientConfig $config */
        $config = $this->config;
        $params = $config->getParams();

        $driverClass = self::DRIVER_MAPPING[$params['driver']] ?? null;
        if ($driverClass === null) {
            throw new RuntimeException(\sprintf('Driver "%s" not supported', $params['driver']));
        }

        $pdoDsnFactory = new ReflectionMethod($driverClass, 'constructPdoDsn');
        $pdoDsn = $pdoDsnFactory->invoke(new $driverClass(), $params);

        $this->__object = new PDO(
            dsn: $pdoDsn,
            username: $params['user'],
            password: $params['password'],
            options: $params['driverOptions'] ?? [],
        );
    }
}
