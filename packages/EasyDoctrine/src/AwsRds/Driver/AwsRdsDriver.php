<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Driver;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use SensitiveParameter;

final class AwsRdsDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        protected readonly AwsRdsConnectionParamsResolver $connectionParamsResolver,
    ) {
        parent::__construct($driver);
    }

    public function connect(#[SensitiveParameter] array $params): ConnectionInterface
    {
        return parent::connect($this->connectionParamsResolver->resolve($params));
    }
}
