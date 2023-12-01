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
use EonX\EasySwoole\Bridge\Doctrine\Coroutine\Enum\CoroutinePdoDriverOption;
use EonX\EasySwoole\Helpers\OutputHelper;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use EonX\EasySwoole\Runtime\EasySwooleRunner;
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
        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'Driver::connect() - start')
        );

        $request = $this->requestStack->getCurrentRequest();
        if ($request?->attributes->get(RequestAttributesInterface::EASY_SWOOLE_ENABLED) !== true) {
            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'Driver::connect() - in swoole disabled statement')
            );

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
            OutputHelper::writeln(
                \sprintf(EasySwooleRunner::LOG_PATTERN, 'Driver::connect() - creating pool')
            );

            $this->logger?->debug(\sprintf('Coroutine PDO Pool "%s" not found, instantiating new one', $poolName));

            $pool = new PDOClientPool(
                factory: new PDOClientFactory(),
                config: new PDOClientConfig($params, $this->connectionParamsResolver, $this->logger),
                size: $poolSize ?? $this->defaultPoolSize,
                heartbeat: $poolHeartbeat ?? $this->defaultHeartbeat,
                maxIdleTime: $poolMaxIdleTime ?? $this->defaultMaxIdleTime,
            );

            // Set pool for new requests
            $_SERVER[$poolName] = $pool;
        }

        OutputHelper::writeln(
            \sprintf(EasySwooleRunner::LOG_PATTERN, 'Driver::connect() - before return')
        );

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
