<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Middleware;

use Doctrine\Bundle\DoctrineBundle\Middleware\ConnectionNameAwareInterface;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use EonX\EasyBugsnag\Doctrine\Driver\Driver;
use EonX\EasyBugsnag\Doctrine\Logger\BreadcrumbLogger;

final class Middleware implements MiddlewareInterface, ConnectionNameAwareInterface
{
    private string $connectionName;

    public function __construct(
        private readonly BreadcrumbLogger $breadcrumbLogger,
    ) {
    }

    public function setConnectionName(string $name): void
    {
        $this->connectionName = $name;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->breadcrumbLogger, $this->connectionName);
    }
}
