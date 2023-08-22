<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
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

        $request = $this->requestStack->getCurrentRequest();
        if ($request?->attributes->get(RequestAttributesInterface::EASY_SWOOLE_ENABLED) !== true) {
            return $connection;
        }

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
