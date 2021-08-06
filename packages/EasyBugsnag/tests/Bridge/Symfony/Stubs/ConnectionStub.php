<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Symfony\Stubs;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ParameterType;

final class ConnectionStub implements Connection
{
    /**
     * @return void
     */
    public function beginTransaction()
    {
        // TODO: Implement beginTransaction() method.
    }

    /**
     * @return void
     */
    public function commit()
    {
        // TODO: Implement commit() method.
    }

    /**
     * @return void
     */
    public function errorCode()
    {
        // TODO: Implement errorCode() method.
    }

    /**
     * @return void
     */
    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }

    /**
     * @param string $sql
     *
     * @return void
     */
    public function exec($sql)
    {
        // TODO: Implement exec() method.
    }

    /**
     * @param null $name
     *
     * @return void
     */
    public function lastInsertId($name = null)
    {
        // TODO: Implement lastInsertId() method.
    }

    /**
     * @param string $sql
     *
     * @return void
     */
    public function prepare($sql)
    {
        // TODO: Implement prepare() method.
    }

    /**
     * @return void
     */
    public function query()
    {
        // TODO: Implement query() method.
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

    /**
     * @return void
     */
    public function rollBack()
    {
        // TODO: Implement rollBack() method.
    }
}
