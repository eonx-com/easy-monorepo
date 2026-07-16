<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Factory;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\AwsRds\Middleware\AwsRdsMiddleware;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Doctrine\Driver\DbalDriver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

if (\method_exists(Connection::class, 'getEventManager')) {
    \class_alias(CoroutineConnectionDbal3Factory::class, CoroutineConnectionFactory::class);
} else {
    final readonly class CoroutineConnectionFactory
    {
        public function __construct(
            private RequestStack $requestStack,
            private ConnectionFactory $factory,
            private int $defaultPoolSize,
            private bool $defaultHeartbeat,
            private float $defaultMaxIdleTime,
            private ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
            private ?LoggerInterface $logger = null,
        ) {}

        /**
         * @param array<string, string>|null $mappingTypes
         *
         * @throws \Doctrine\DBAL\Exception
         */
        public function createConnection(
            array $params,
            ?Configuration $config = null,
            ?array $mappingTypes = null,
        ): Connection {
            $mappingTypes ??= [];
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
                // The Coroutine PDO pool (DbalDriver) already resolves AWS RDS connection params
                // (IAM auth token + SSL) on the original params via the injected resolver. Re-wrapping
                // with AwsRdsMiddleware would resolve a second time on already-stripped params, dropping
                // driver options such as the cross-account assume-role ARN from the pooled connection's
                // token. Skip it when the pool can resolve params itself
                if ($this->connectionParamsResolver !== null && $middleware instanceof AwsRdsMiddleware) {
                    continue;
                }

                $driver = $middleware->wrap($driver);
            }

            $connectionClass = $connection::class;

            $coroutineConnection = new $connectionClass(
                $connection->getParams(),
                $driver,
                $connection->getConfiguration()
            );

            if (\count($mappingTypes) > 0) {
                $platform = $coroutineConnection->getDatabasePlatform();
                foreach ($mappingTypes as $dbType => $doctrineType) {
                    $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
                }
            }

            return $coroutineConnection;
        }
    }
}
