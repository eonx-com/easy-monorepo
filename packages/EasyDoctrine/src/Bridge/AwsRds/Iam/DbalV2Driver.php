<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Iam;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class DbalV2Driver implements Driver
{
    public function __construct(
        private readonly AuthTokenProvider $authTokenProvider,
        private readonly Driver $decorated
    ) {
    }

    /**
     * @param mixed[] $params
     * @param null|string $username
     * @param null|string $password
     * @param mixed[] $driverOptions
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = []): Connection
    {
        $username = $params['user'] ?? $username;
        $password = $this->authTokenProvider->getAuthToken([
            'driverOptions' => $driverOptions,
            'host' => $params['host'],
            'port' => $params['port'],
            'user' => $username,
        ]);

        return $this->decorated->connect($params, $username, $password, $driverOptions);
    }

    public function getDatabase(Connection $conn): string
    {
        return $this->decorated->getDatabase($conn);
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->decorated->getDatabasePlatform();
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function getSchemaManager(Connection $conn): AbstractSchemaManager
    {
        return $this->decorated->getSchemaManager($conn);
    }
}
