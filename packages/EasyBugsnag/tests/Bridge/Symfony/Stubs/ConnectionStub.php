<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;

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

    /**
     * @param null $name
     */
    public function lastInsertId($name = null): string
    {
        return 'last-insert-id';
    }

    /**
     * @param string $sql
     */
    public function prepare($sql): void
    {
    }

    public function query(): void
    {
    }

    /**
     * @param mixed $value
     * @param int $type
     *
     * @return void
     */
    public function quote($value, $type = ParameterType::STRING)
    {
        // TODO: Implement quote() method.
    }

    public function rollBack(): bool
    {
        return false;
    }
}
