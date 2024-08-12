<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Driver;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use EonX\EasyBugsnag\Doctrine\Connection\BreadcrumbLoggerConnection;
use EonX\EasyBugsnag\Doctrine\Logger\QueryBreadcrumbLogger;
use SensitiveParameter;

final class BreadcrumbLoggerDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private readonly QueryBreadcrumbLogger $queryBreadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($driver);
    }

    public function connect(#[SensitiveParameter] array $params): ConnectionInterface
    {
        return new BreadcrumbLoggerConnection(
            parent::connect($params),
            $this->queryBreadcrumbLogger,
            $this->connectionName
        );
    }
}
