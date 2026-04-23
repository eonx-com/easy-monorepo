<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stub\Connection;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use RuntimeException;

/**
 * @deprecated Remove when Doctrine DBAL 3 support is dropped.
 */
final class BrokenDbal3Connection implements Connection
{
    public function beginTransaction()
    {
        throw new RuntimeException('Dummy');
    }

    public function commit()
    {
        throw new RuntimeException('Dummy');
    }

    public function exec(string $sql): int
    {
        throw new RuntimeException('Dummy');
    }

    public function getNativeConnection(): mixed
    {
        throw new RuntimeException('Dummy');
    }

    public function getServerVersion(): string
    {
        throw new RuntimeException('Dummy');
    }

    public function lastInsertId($name = null)
    {
        throw new RuntimeException('Dummy');
    }

    public function prepare(string $sql): Statement
    {
        throw new RuntimeException('Dummy');
    }

    public function query(string $sql): Result
    {
        throw new RuntimeException('Dummy');
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        throw new RuntimeException('Dummy');
    }

    public function rollBack()
    {
        throw new RuntimeException('Dummy');
    }
}
