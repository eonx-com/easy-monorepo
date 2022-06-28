<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AuthTokenConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly CacheInterface $cache,
        private readonly string $awsRegion,
        private readonly string $awsUsername,
        private readonly int $cacheExpiryInSeconds
    ) {
    }

    /**
     * @param mixed[] $params
     * @param array<string, string>|null $mappingTypes
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
        ?array $mappingTypes = null
    ): Connection {
        $params['password'] = $this->generatePassword($params);

        return $this->factory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

    /**
     * @param mixed[] $params
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function generatePassword(array $params): string
    {
        $key = \sprintf('easy-doctrine-pwd-%s', $this->awsUsername);

        return $this->cache->get($key, function (ItemInterface $item) use ($params): string {
            $item->expiresAfter($this->cacheExpiryInSeconds);

            $endpoint = \sprintf('%s:%s', $params['host'], $params['port']);
            $tokenGenerator = new AuthTokenGenerator(CredentialProvider::defaultProvider());

            return $tokenGenerator->createToken($endpoint, $this->awsRegion, $this->awsUsername);
        });
    }
}
