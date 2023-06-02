<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsOptionsInterface;
use EonX\EasyDoctrine\Bridge\AwsRds\Iam\AuthTokenProvider;
use EonX\EasyDoctrine\Bridge\AwsRds\Iam\DbalV2Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\Iam\DbalV3Driver;
use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;

final class AuthTokenConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly AuthTokenProvider $authTokenProvider,
        private readonly ?string $sslMode = null,
        private readonly ?CertificateAuthorityProvider $certificateAuthorityProvider = null
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
        $driverOptions = $params['driverOptions'] ?? [];
        $rdsIamEnabled = $driverOptions[AwsRdsOptionsInterface::IAM_ENABLED] ?? true;
        $rdsSslEnabled = $driverOptions[AwsRdsOptionsInterface::SSL_ENABLED] ?? true;

        unset(
            $params['driverOptions'][AwsRdsOptionsInterface::IAM_ENABLED],
            $params['driverOptions'][AwsRdsOptionsInterface::SSL_ENABLED],
        );

        if ($rdsSslEnabled
            && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_SSL_ENABLED')
            && $this->certificateAuthorityProvider !== null) {
            if ($this->sslMode !== null) {
                $params['sslmode'] ??= $this->sslMode;
            }
            $params['sslrootcert'] = $this->certificateAuthorityProvider->getCertificateAuthorityPath();
        }

        $connection = $this->factory->createConnection($params, $config, $eventManager, $mappingTypes ?? []);

        if ($rdsIamEnabled && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_IAM_ENABLED')) {
            $driverClass = \method_exists(Driver::class, 'getExceptionConverter')
                ? DbalV3Driver::class
                : DbalV2Driver::class;

            $connectionClass = $connection::class;
            $connection = new $connectionClass(
                $connection->getParams(),
                new $driverClass($this->authTokenProvider, $connection->getDriver()),
                $connection->getConfiguration(),
                $connection->getEventManager()
            );
        }

        return $connection;
    }

    private function isEnabled(string $feature): bool
    {
        return ($_SERVER[$feature] ?? $_ENV[$feature] ?? 'enabled') === 'enabled';
    }
}
