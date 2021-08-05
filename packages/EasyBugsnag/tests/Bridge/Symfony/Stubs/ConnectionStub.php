<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;

final class ConnectionStub implements Connection
{
    public function prepare($sql)
    {
        // TODO: Implement prepare() method.
    }

    public function query()
    {
        // TODO: Implement query() method.
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        // TODO: Implement quote() method.
    }

    public function exec($sql)
    {
        // TODO: Implement exec() method.
    }

    public function lastInsertId($name = null)
    {
        // TODO: Implement lastInsertId() method.
    }

    public function beginTransaction()
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollBack()
    {
        // TODO: Implement rollBack() method.
    }

    public function errorCode()
    {
        // TODO: Implement errorCode() method.
    }

    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }
}
