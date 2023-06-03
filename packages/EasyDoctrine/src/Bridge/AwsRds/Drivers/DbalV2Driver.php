<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds\Drivers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class DbalV2Driver extends AbstractAwsRdsDriver implements Driver
{
    /**
     * @param mixed[] $params
     * @param mixed[] $driverOptions
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function connect(array $params, ?string $username = null, ?string $password = null, ?array $driverOptions = null): Connection
    {
        $params = $this->connectionParamsResolver->getParams(\array_merge($params, [
            'driverOptions' => $driverOptions,
            'password' => $password,
            'user' => $username,
        ]));

        $username = $params['user'] ?? null;
        $password = $password['password'] ?? null;
        $driverOptions = $params['driverOptions'] ?? [];

        unset($params['driverOptions'], $params['password'], $params['user']);

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
