<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Connection;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use EonX\EasyBugsnag\Doctrine\Logger\BreadcrumbLogger;
use EonX\EasyBugsnag\Doctrine\Statement\Statement;
use EonX\EasyBugsnag\Doctrine\ValueObject\Query;

final class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
        private readonly BreadcrumbLogger $breadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($connection);
    }

    public function beginTransaction(): void
    {
        $query = new Query('"START TRANSACTION"');

        try {
            parent::beginTransaction();
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }
    }

    public function commit(): void
    {
        $query = new Query('"COMMIT"');

        try {
            parent::commit();
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }
    }

    public function exec(string $sql): int|string
    {
        $query = new Query($sql);

        try {
            $affectedRows = parent::exec($sql);
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }

        return $affectedRows;
    }

    public function prepare(string $sql): Statement
    {
        return new Statement(parent::prepare($sql), $sql, $this->breadcrumbLogger, $this->connectionName);
    }

    public function query(string $sql): Result
    {
        $query = new Query($sql);

        try {
            return parent::query($sql);
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }
    }

    public function rollBack(): void
    {
        $query = new Query('"ROLLBACK"');

        try {
            parent::rollBack();
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }
    }
}
