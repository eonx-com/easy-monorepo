<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Connection;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use EonX\EasyBugsnag\Doctrine\Logger\QueryBreadcrumbLogger;
use EonX\EasyBugsnag\Doctrine\Statement\BreadcrumbLoggerStatement;
use EonX\EasyBugsnag\Doctrine\ValueObject\QueryBreadcrumb;

final class BreadcrumbLoggerConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
        private readonly QueryBreadcrumbLogger $queryBreadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($connection);
    }

    public function beginTransaction(): void
    {
        $queryBreadcrumb = new QueryBreadcrumb('"START TRANSACTION"', $this->connectionName);

        try {
            parent::beginTransaction();
        } finally {
            $this->queryBreadcrumbLogger->log($queryBreadcrumb);
        }
    }

    public function commit(): void
    {
        $queryBreadcrumb = new QueryBreadcrumb('"COMMIT"', $this->connectionName);

        try {
            parent::commit();
        } finally {
            $this->queryBreadcrumbLogger->log($queryBreadcrumb);
        }
    }

    public function exec(string $sql): int
    {
        $queryBreadcrumb = new QueryBreadcrumb($sql, $this->connectionName);

        try {
            return parent::exec($sql);
        } finally {
            $this->queryBreadcrumbLogger->log($queryBreadcrumb);
        }
    }

    public function prepare(string $sql): BreadcrumbLoggerStatement
    {
        return new BreadcrumbLoggerStatement(
            parent::prepare($sql),
            $sql,
            $this->queryBreadcrumbLogger,
            $this->connectionName
        );
    }

    public function query(string $sql): Result
    {
        $queryBreadcrumb = new QueryBreadcrumb($sql, $this->connectionName);

        try {
            return parent::query($sql);
        } finally {
            $this->queryBreadcrumbLogger->log($queryBreadcrumb);
        }
    }

    public function rollBack(): void
    {
        $queryBreadcrumb = new QueryBreadcrumb('"ROLLBACK"', $this->connectionName);

        try {
            parent::rollBack();
        } finally {
            $this->queryBreadcrumbLogger->log($queryBreadcrumb);
        }
    }
}
