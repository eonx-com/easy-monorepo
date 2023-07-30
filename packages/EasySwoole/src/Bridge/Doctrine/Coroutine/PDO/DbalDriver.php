<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use SensitiveParameter;

final class DbalDriver implements Driver
{
    private const POOL_NAME_PATTERN = 'coroutine_pdo_pool_%s';

    public function __construct(
        private readonly Driver $decorated,
        private readonly int $defaultPoolSize,
        private readonly bool $defaultHeartbeat,
        private readonly float $defaultMaxIdleTime,
        private readonly ?AwsRdsConnectionParamsResolver $connectionParamsResolver = null,
    ) {
    }

    public function connect(#[SensitiveParameter] array $params): DriverConnection
    {
        $poolName = \sprintf(self::POOL_NAME_PATTERN, $this->getOption(ValueOptionInterface::POOL_NAME, $params));
        $poolSize = $this->getOption(ValueOptionInterface::POOL_SIZE, $params);
        $poolHeartbeat = $this->getOption(ValueOptionInterface::POOL_HEARTBEAT, $params);
        $poolMaxIdleTime = $this->getOption(ValueOptionInterface::POOL_MAX_IDLE_TIME, $params);

        unset(
            $params['driverOptions'][ValueOptionInterface::POOL_HEARTBEAT],
            $params['driverOptions'][ValueOptionInterface::POOL_MAX_IDLE_TIME],
            $params['driverOptions'][ValueOptionInterface::POOL_NAME],
            $params['driverOptions'][ValueOptionInterface::POOL_SIZE],
        );

        $pool = $_SERVER[$poolName] ?? null;
        if ($pool === null) {
            $pool = new PDOClientPool(
                factory: new PDOClientFactory(),
                config: new PDOClientConfig($params, $this->connectionParamsResolver),
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

    private function getOption(string $name, array $params): mixed
    {
        return $params['driverOptions'][$name] ?? null;
    }
}
