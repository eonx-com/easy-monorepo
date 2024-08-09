<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Driver;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use EonX\EasyBugsnag\Doctrine\Connection\Connection;
use EonX\EasyBugsnag\Doctrine\Logger\BreadcrumbLogger;
use SensitiveParameter;

final class Driver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private readonly BreadcrumbLogger $breadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($driver);
    }

    public function connect(#[SensitiveParameter] array $params): ConnectionInterface
    {
        return new Connection(parent::connect($params), $this->breadcrumbLogger, $this->connectionName);
    }
}
