<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use EonX\EasyDoctrine\Bridge\AwsRds\Drivers\DbalV2Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\Drivers\DbalV3Driver;

final class AuthTokenConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly AwsRdsConnectionParamsResolver $connectionParamsResolver,
    ) {
    }

    /**
     * @param mixed[] $params
     * @param array<string, string>|null $mappingTypes
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
        ?array $mappingTypes = null,
    ): Connection {
        $connection = $this->factory->createConnection($params, $config, $eventManager, $mappingTypes ?? []);

        $connectionClass = $connection::class;
        $driverClass = \method_exists(Driver::class, 'getExceptionConverter')
            ? DbalV3Driver::class
            : DbalV2Driver::class;

        return new $connectionClass(
            $connection->getParams(),
            new $driverClass($connection->getDriver(), $this->connectionParamsResolver),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }
}
