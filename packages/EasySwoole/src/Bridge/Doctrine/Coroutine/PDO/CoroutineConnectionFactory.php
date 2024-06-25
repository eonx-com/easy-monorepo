<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class CoroutineConnectionFactory extends ConnectionFactory
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
        // Call parent constructor with empty values as we only extend the factory from
        // doctrine bundle as it does not implement an interface allowing us to have multiple decoration
        // and inject the inner connection in the decorator
        parent::__construct([], null);
    }

    /**
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
            $connection->getConfiguration(),
            $connection->getEventManager()
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
