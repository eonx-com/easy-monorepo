<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Factory;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Doctrine\Driver\DbalDriver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class CoroutineConnectionFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConnectionFactory $factory,
        private readonly int $defaultPoolSize,
        private readonly bool $defaultHeartbeat,
        private readonly float $defaultMaxIdleTime,
        private readonly ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $mappingTypes
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        array $mappingTypes = [],
    ): Connection {
        $connection = $this->factory->createConnection($params, $config, $mappingTypes);

        $driver = new DbalDriver(
            $connection->getDriver(),
            $this->defaultPoolSize,
            $this->defaultHeartbeat,
            $this->defaultMaxIdleTime,
            $this->requestStack,
            $this->connectionParamsResolver,
            $this->logger
        );

        foreach ($config?->getMiddlewares() ?? [] as $middleware) {
            $driver = $middleware->wrap($driver);
        }

        $connectionClass = $connection::class;

        $coroutineConnection = new $connectionClass(
            $connection->getParams(),
            $driver,
            $connection->getConfiguration()
        );

        if (\count($mappingTypes ?? []) > 0) {
            $platform = $coroutineConnection->getDatabasePlatform();
            foreach ($mappingTypes as $dbType => $doctrineType) {
                $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        return $coroutineConnection;
    }
}
