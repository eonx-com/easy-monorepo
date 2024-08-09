<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Statement;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use EonX\EasyBugsnag\Doctrine\Logger\BreadcrumbLogger;
use EonX\EasyBugsnag\Doctrine\ValueObject\Query;

final class Statement extends AbstractStatementMiddleware
{
    private readonly Query $query;

    public function __construct(
        StatementInterface $statement,
        string $sql,
        private readonly BreadcrumbLogger $breadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($statement);

        $this->query = new Query($sql);
    }

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        $this->query->setValue($param, $value, $type);

        parent::bindValue($param, $value, $type);
    }

    public function execute(): ResultInterface
    {
        // Clone to prevent variables by reference to change
        $query = clone $this->query;
        $query->start();

        try {
            return parent::execute();
        } finally {
            $this->breadcrumbLogger->log($this->connectionName, $query);
        }
    }
}
