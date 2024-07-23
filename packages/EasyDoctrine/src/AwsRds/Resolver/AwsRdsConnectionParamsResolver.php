<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Resolver;

use EonX\EasyDoctrine\AwsRds\Enum\AwsRdsOption;
use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProvider;
use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsCertificateAuthorityProvider;
use PDO;

final readonly class AwsRdsConnectionParamsResolver
{
    public function __construct(
        private ?AwsRdsAuthTokenProvider $authTokenProvider = null,
        private ?string $sslMode = null,
        private ?AwsRdsCertificateAuthorityProvider $certificateAuthorityProvider = null,
        private ?string $awsUsername = null,
    ) {
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getParams(array $params): array
    {
        $driverOptions = $params['driverOptions'] ?? [];
        $rdsIamEnabled = $driverOptions[AwsRdsOption::IamEnabled->value] ?? true;
        $rdsSslEnabled = $driverOptions[AwsRdsOption::SslEnabled->value] ?? true;

        if ($rdsIamEnabled
            && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_IAM_ENABLED')
            && $this->authTokenProvider !== null) {
            // Override username with aws one if provided to provide auth issue with db
            $params['user'] = $params['driverOptions'][AwsRdsOption::Username->value]
                ?? $this->awsUsername
                ?? $params['user'];

            $params['password'] = $this->authTokenProvider->getAuthToken($params);
        }

        if ($rdsSslEnabled
            && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_SSL_ENABLED')
            && $this->certificateAuthorityProvider !== null) {
            $caPath = $this->certificateAuthorityProvider->getCertificateAuthorityPath();

            if ($params['driver'] === 'pdo_mysql') {
                $params['driverOptions'][PDO::MYSQL_ATTR_SSL_CA] = $caPath;
            }

            if ($params['driver'] === 'pdo_pgsql') {
                $sslMode = $driverOptions[AwsRdsOption::SslMode->value] ?? $this->sslMode;
                if ($sslMode !== null) {
                    $params['sslmode'] = $sslMode;
                }

                $params['sslrootcert'] = $caPath;
            }
        }

        foreach (AwsRdsOption::cases() as $option) {
            unset($params['driverOptions'][$option->value]);
        }

        return $params;
    }

    private function isEnabled(string $feature): bool
    {
        return ($_SERVER[$feature] ?? $_ENV[$feature] ?? 'enabled') === 'enabled';
    }
}
