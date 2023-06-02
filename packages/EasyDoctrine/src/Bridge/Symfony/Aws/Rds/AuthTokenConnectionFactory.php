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
    public const OPTION_AWS_RDS_IAM_ENABLED = 'easy_doctrine_aws_rds_iam_enabled';

    public const OPTION_AWS_RDS_IAM_USERNAME = 'easy_doctrine_aws_rds_iam_username';

    public const OPTION_AWS_RDS_SSL_ENABLED = 'easy_doctrine_aws_rds_ssl_enabled';

    public const OPTION_AWS_RDS_SSL_MODE = 'easy_doctrine_aws_rds_ssl_mode';

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
        private readonly string $sslCertDir,
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
        ?array $mappingTypes = null,
    ): Connection {
        $driverOptions = $params['driverOptions'] ?? [];
        $rdsIamEnabled = $driverOptions[self::OPTION_AWS_RDS_IAM_ENABLED] ?? true;
        $rdsSslEnabled = $driverOptions[self::OPTION_AWS_RDS_SSL_ENABLED] ?? $this->sslEnabled;

        if ($rdsIamEnabled && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_IAM_ENABLED')) {
            $params['authTokenGenerator'] = new AuthTokenGenerator(CredentialProvider::defaultProvider());
            $params['user'] = $driverOptions[self::OPTION_AWS_RDS_IAM_USERNAME] ?? $this->awsUsername;
            $params['passwordGenerator'] = $this->getGeneratePasswordClosure();
            $params['wrapperClass'] = RdsIamConnection::class;
        }

        if ($rdsSslEnabled && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_SSL_ENABLED')) {
            $params['sslmode'] = $driverOptions[self::OPTION_AWS_RDS_SSL_MODE] ?? $this->sslMode;
            $params['sslrootcert'] = $this->resolveSslCertPath();
        }

        unset(
            $params['driverOptions'][self::OPTION_AWS_RDS_IAM_ENABLED],
            $params['driverOptions'][self::OPTION_AWS_RDS_IAM_USERNAME],
            $params['driverOptions'][self::OPTION_AWS_RDS_SSL_ENABLED],
            $params['driverOptions'][self::OPTION_AWS_RDS_SSL_MODE]
        );

        return $this->factory->createConnection($params, $config, $eventManager, $mappingTypes ?? []);
    }

    private function getGeneratePasswordClosure(): \Closure
    {
        return function (AuthTokenGenerator $tokenGenerator, array $params): string {
            $key = \sprintf('easy-doctrine-pwd-%s', $params['user'] ?? $this->awsUsername);

            return $this->cache->get($key, function (ItemInterface $item) use ($tokenGenerator, $params): string {
                $item->expiresAfter($this->cacheExpiryInSeconds);

                return $tokenGenerator->createToken(
                    \sprintf('%s:%s', $params['host'], $params['port']),
                    $this->awsRegion,
                    $params['user'] ?? $this->awsUsername
                );
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
