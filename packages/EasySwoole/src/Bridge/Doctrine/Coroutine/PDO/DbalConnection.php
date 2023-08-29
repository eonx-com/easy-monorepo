<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\ParameterTypeMap;
use Doctrine\DBAL\Driver\PDO\PDOException as DriverPDOException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use OpenSwoole\Core\Coroutine\Pool\ClientPool;
use PDO;
use PDOException;

final class DbalConnection implements Connection
{
    private ?PDOClient $pdo = null;

    public function __construct(
        private readonly ClientPool $pool,
    ) {
    }

    public function __destruct()
    {
        if ($this->pdo !== null) {
            $this->pool->put($this->pdo);
        }
    }

    public function beginTransaction(): bool
    {
        try {
            return $this->getPdo()
                ->beginTransaction();
        } catch (PDOException $exception) {
            throw DriverPDOException::new($exception);
        }
    }

    public function commit(): bool
    {
        try {
            return $this->getPdo()
                ->commit();
        } catch (PDOException $exception) {
            throw DriverPDOException::new($exception);
        }
    }

    public function exec(string $sql): int
    {
        return $this->getPdo()
            ->exec($sql);
    }

    public function getNativeConnection(): object
    {
        $pdo = $this->getPdo();
        // Because this function returns an instance of the base \PDO class,
        // consumers will not trigger the last used time on the PDOClient instance.
        // So we need to trigger it explicitly to allow PDOClientPool to close it once it reaches idle max time
        $pdo->triggerLastUsedTime();

        /** @var \PDO $basePdo */
        $basePdo = $pdo->__getObject();
        $basePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $basePdo;
    }

    public function lastInsertId($name = null)
    {
        try {
            return $name === null
                ? $this->getPdo()
                    ->lastInsertId()
                : $this->getPdo()
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

    /**
     * @param string $value
     * @param int $type
     *
     * @throws \Doctrine\DBAL\Driver\Exception\UnknownParameterType
     */
    public function quote($value, $type = ParameterType::STRING): mixed
    {
        return $this->getPdo()
            ->quote($value, ParameterTypeMap::convertParamType($type));
    }

    public function rollBack(): bool
    {
        try {
            return $this->getPdo()
                ->rollBack();
        } catch (PDOException $exception) {
            throw DriverPDOException::new($exception);
        }
    }

    private function getPdo(): PDOClient
    {
        return $this->pdo ??= $this->pool->get();
    }
}
