<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stub\Connection;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use RuntimeException;

final class BrokenConnection implements Connection
{
    public function beginTransaction(): bool
    {
        throw new RuntimeException('Dummy');
    }

    public function commit(): bool
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

    public function lastInsertId($name = null): false|int|string
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

    public function quote(mixed $value, $type = ParameterType::STRING): string
    {
        throw new RuntimeException('Dummy');
    }

    public function rollBack(): bool
    {
        throw new RuntimeException('Dummy');
    }
}
