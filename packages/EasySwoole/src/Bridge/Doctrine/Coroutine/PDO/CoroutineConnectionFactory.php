<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;

final class CoroutineConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly int $defaultPoolSize,
        private readonly bool $defaultHeartbeat,
        private readonly float $defaultMaxIdleTime,
        private readonly ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
    ) {
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
        $connectionClass = $connection::class;

        return new $connectionClass(
            $connection->getParams(),
            new DbalDriver(
                $connection->getDriver(),
                $this->defaultPoolSize,
                $this->defaultHeartbeat,
                $this->defaultMaxIdleTime,
                $this->connectionParamsResolver,
            ),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }
}
