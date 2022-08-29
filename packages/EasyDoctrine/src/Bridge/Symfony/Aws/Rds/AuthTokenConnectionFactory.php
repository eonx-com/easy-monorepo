<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AuthTokenConnectionFactory
{
    private const RDS_COMBINED_CERT_FILENAME_PATTERN = '%s/rds-combined-ca-bundle.pem';

    private const RDS_COMBINED_CERT_URL = 'https://s3.amazonaws.com/rds-downloads/rds-combined-ca-bundle.pem';

    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly CacheInterface $cache,
        private readonly string $awsRegion,
        private readonly string $awsUsername,
        private readonly int $cacheExpiryInSeconds,
        private readonly bool $sslEnabled,
        private readonly string $sslMode,
        private readonly string $sslCertDir
    ) {
    }

    /**
     * @param mixed[] $params
     * @param array<string, string>|null $mappingTypes
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
        ?array $mappingTypes = null
    ): Connection {
        if ($this->isEnabled('EASY_DOCTRINE_AWS_RDS_IAM_ENABLED')) {
            $params['user'] = $this->awsUsername;
            $params['passwordGenerator'] = $this->getGeneratePasswordClosure();
            $params['wrapperClass'] = RdsIamConnection::class;
        }

        if ($this->sslEnabled && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_SSL_ENABLED')) {
            $params['sslmode'] = $this->sslMode;
            $params['sslrootcert'] = $this->resolveSslCertPath();
        }

        return $this->factory->createConnection($params, $config, $eventManager, $mappingTypes ?? []);
    }

    private function getGeneratePasswordClosure(): \Closure
    {
        return function (array $params): string {
            $key = \sprintf('easy-doctrine-pwd-%s', $this->awsUsername);

            return $this->cache->get($key, function (ItemInterface $item) use ($params): string {
                $item->expiresAfter($this->cacheExpiryInSeconds);

                $endpoint = \sprintf('%s:%s', $params['host'], $params['port']);
                $tokenGenerator = new AuthTokenGenerator(CredentialProvider::defaultProvider());

                return $tokenGenerator->createToken($endpoint, $this->awsRegion, $this->awsUsername);
            });
        };
    }

    private function isEnabled(string $feature): bool
    {
        return ($_SERVER[$feature] ?? $_ENV[$feature] ?? 'enabled') === 'enabled';
    }

    private function resolveSslCertPath(): string
    {
        $filesystem = new Filesystem();
        $filename = \sprintf(self::RDS_COMBINED_CERT_FILENAME_PATTERN, $this->sslCertDir);

        if ($filesystem->exists($filename) === false) {
            $certContents = \file_get_contents(self::RDS_COMBINED_CERT_URL);

            if (\is_string($certContents) === false) {
                throw new \RuntimeException('Could not download RDS Combined Cert');
            }

            $filesystem->dumpFile($filename, $certContents);
        }

        return $filename;
    }
}
