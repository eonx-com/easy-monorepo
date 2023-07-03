<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

final class ConnectionStub implements Connection
{
    public function beginTransaction(): bool
    {
        return false;
    }

    public function commit(): bool
    {
        return false;
    }

    public function errorCode(): ?string
    {
        return 'errorCode';
    }

    /**
     * @return mixed[]
     */
    public function errorInfo(): array
    {
        return [];
    }

    /**
     * @param string $sql
     */
    public function exec($sql): int
    {
        return 0;
    }

    public function lastInsertId(mixed $name = null): string
    {
        return 'last-insert-id';
    }

    public function prepare(string $sql): Statement
    {
        return new StatementStub();
    }

    public function query(string $sql): Result
    {
        return new ResultStub();
    }

    /**
     * @param mixed $value
     * @param int $type
     */
    public function quote(mixed $value, mixed $type = null): void
    {
    }

    public function rollBack(): bool
    {
        return false;
    }
}
