<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stub\Connection;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use RuntimeException;

final class BrokenConnection implements Connection
{
    public function beginTransaction(): void
    {
        throw new RuntimeException('Dummy');
    }

    public function commit(): void
    {
        throw new RuntimeException('Dummy');
    }

    public function exec(string $sql): int|string
    {
        throw new RuntimeException('Dummy');
    }

    public function getNativeConnection()
    {
        throw new RuntimeException('Dummy');
    }

    public function getServerVersion(): string
    {
        throw new RuntimeException('Dummy');
    }

    public function lastInsertId(): int|string
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

    public function quote(string $value): string
    {
        throw new RuntimeException('Dummy');
    }

    public function rollBack(): void
    {
        throw new RuntimeException('Dummy');
    }
}
