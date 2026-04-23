<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Connection;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use EonX\EasySwoole\Doctrine\Client\PdoClient;
use EonX\EasySwoole\Doctrine\Pool\PdoClientPool;
use EonX\EasySwoole\Doctrine\Result\DbalResult;
use EonX\EasySwoole\Doctrine\Statement\DbalStatement;
use PDO;
use PDOException;

/**
 * @deprecated Remove when Doctrine DBAL 3 support is dropped.
 */
final class Dbal3Connection implements Connection
{
    private ?PdoClient $pdo = null;

    public function __construct(
        private readonly PdoClientPool $pool,
    ) {
    }

    public function __destruct()
    {
        if ($this->pdo !== null) {
            $this->pool->put($this->pdo);
        }
    }

    public function beginTransaction()
    {
        return $this->getPdo()
            ->beginTransaction();
    }

    public function commit()
    {
        return $this->getPdo()
            ->commit();
    }

    public function exec(string $sql): int
    {
        return $this->getPdo()
            ->exec($sql);
    }

    public function getNativeConnection(): object
    {
        $pdo = $this->getPdo();
        $pdo->triggerLastUsedTime();

        /** @var \PDO $basePdo */
        $basePdo = $pdo->__getObject();
        $basePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $basePdo;
    }

    public function getServerVersion(): string
    {
        return $this->getPdo()
            ->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public function lastInsertId($name = null)
    {
        try {
            return $this->getPdo()
                ->lastInsertId($name);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function prepare(string $sql): Statement
    {
        try {
            return new DbalStatement($this->getPdo()->prepare($sql));
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function query(string $sql): Result
    {
        try {
            return new DbalResult($this->getPdo()->query($sql));
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->getPdo()
            ->quote($value, $type);
    }

    public function rollBack()
    {
        return $this->getPdo()
            ->rollBack();
    }

    private function getPdo(): PdoClient
    {
        return $this->pdo ??= $this->pool->get();
    }
}
