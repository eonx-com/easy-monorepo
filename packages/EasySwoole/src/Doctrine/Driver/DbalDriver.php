<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use EonX\EasyDoctrine\AwsRds\Resolver\AwsRdsConnectionParamsResolver;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use EonX\EasySwoole\Doctrine\ClientConfig\PdoClientConfig;
use EonX\EasySwoole\Doctrine\Connection\DbalConnection;
use EonX\EasySwoole\Doctrine\Enum\CoroutinePdoDriverOption;
use EonX\EasySwoole\Doctrine\Factory\PdoClientFactory;
use EonX\EasySwoole\Doctrine\Pool\PdoClientPool;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\RequestStack;

final class DbalDriver implements Driver
{
    private const POOL_NAME_PATTERN = 'coroutine_pdo_pool_%s';

    public function __construct(
        private readonly Driver $decorated,
        private readonly int $defaultPoolSize,
        private readonly bool $defaultHeartbeat,
        private readonly float $defaultMaxIdleTime,
        private readonly RequestStack $requestStack,
        private readonly ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function connect(#[SensitiveParameter] array $params): DriverConnection
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->attributes->get(RequestAttribute::EasySwooleEnabled->value) !== true) {
            $params = $this->connectionParamsResolver?->getParams($params) ?? $params;

            return $this->decorated->connect($params);
        }

        $poolName = \sprintf(self::POOL_NAME_PATTERN, $this->getOption(CoroutinePdoDriverOption::PoolName, $params));
        $poolSize = $this->getOption(CoroutinePdoDriverOption::PoolSize, $params);
        $poolHeartbeat = $this->getOption(CoroutinePdoDriverOption::PoolHeartbeat, $params);
        $poolMaxIdleTime = $this->getOption(CoroutinePdoDriverOption::PoolMaxIdleTime, $params);

        unset(
            $params['driverOptions'][CoroutinePdoDriverOption::PoolHeartbeat->value],
            $params['driverOptions'][CoroutinePdoDriverOption::PoolMaxIdleTime->value],
            $params['driverOptions'][CoroutinePdoDriverOption::PoolName->value],
            $params['driverOptions'][CoroutinePdoDriverOption::PoolSize->value],
        );

        $pool = $_SERVER[$poolName] ?? null;
        if ($pool === null) {
            $this->logger?->debug(\sprintf('Coroutine PDO Pool "%s" not found, instantiating new one', $poolName));

            $pool = new PdoClientPool(
                factory: new PdoClientFactory(),
                config: new PdoClientConfig($params, $this->connectionParamsResolver, $this->logger),
                size: $poolSize ?? $this->defaultPoolSize,
                heartbeat: $poolHeartbeat ?? $this->defaultHeartbeat,
                maxIdleTime: $poolMaxIdleTime ?? $this->defaultMaxIdleTime,
            );

            // Set pool for new requests
            $_SERVER[$poolName] = $pool;
        }

        return new DbalConnection($pool);
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->decorated->getDatabasePlatform();
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return $this->decorated->getExceptionConverter();
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager<\Doctrine\DBAL\Platforms\AbstractPlatform>
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform): AbstractSchemaManager
    {
        return $this->decorated->getSchemaManager($conn, $platform);
    }

    private function getOption(CoroutinePdoDriverOption $driverOption, array $params): mixed
    {
        return $params['driverOptions'][$driverOption->value] ?? null;
    }
}
