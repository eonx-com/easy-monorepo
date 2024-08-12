<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Middleware;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use EonX\EasyDoctrine\AwsRds\Driver\AwsRdsDriver;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;

#[AsMiddleware(priority: 100)]
final readonly class AwsRdsMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected AwsRdsConnectionParamsResolver $connectionParamsResolver,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new AwsRdsDriver($driver, $this->connectionParamsResolver);
    }
}
