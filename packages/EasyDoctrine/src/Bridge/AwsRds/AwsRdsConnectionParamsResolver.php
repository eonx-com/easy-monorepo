<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\AwsRds;

use EonX\EasyDoctrine\Bridge\AwsRds\Iam\AuthTokenProvider;
use EonX\EasyDoctrine\Bridge\AwsRds\Ssl\CertificateAuthorityProvider;

final class AwsRdsConnectionParamsResolver
{
    public function __construct(
        private readonly ?AuthTokenProvider $authTokenProvider = null,
        private readonly ?string $sslMode = null,
        private readonly ?CertificateAuthorityProvider $certificateAuthorityProvider = null,
        private readonly ?string $awsUsername = null,
    ) {
    }

    /**
     * @param mixed[] $params
     *
     * @return mixed[]
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getParams(array $params): array
    {
        $driverOptions = $params['driverOptions'] ?? [];
        $rdsIamEnabled = $driverOptions[AwsRdsOptionsInterface::IAM_ENABLED] ?? true;
        $rdsSslEnabled = $driverOptions[AwsRdsOptionsInterface::SSL_ENABLED] ?? true;

        if ($rdsIamEnabled
            && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_IAM_ENABLED')
            && $this->authTokenProvider !== null) {
            // Override username with aws one if provided to provide auth issue with db
            $params['user'] = $params['driverOptions'][AwsRdsOptionsInterface::AWS_USERNAME]
                ?? $this->awsUsername
                ?? $params['user'];

            $params['password'] = $this->authTokenProvider->getAuthToken($params);
        }

        if ($rdsSslEnabled
            && $this->isEnabled('EASY_DOCTRINE_AWS_RDS_SSL_ENABLED')
            && $this->certificateAuthorityProvider !== null) {
            $sslMode = $driverOptions[AwsRdsOptionsInterface::SSL_MODE] ?? $this->sslMode;
            if ($sslMode !== null) {
                $params['sslmode'] = $sslMode;
            }

            $params['sslrootcert'] = $this->certificateAuthorityProvider->getCertificateAuthorityPath();
        }

        foreach (AwsRdsOptionsInterface::ALL_OPTIONS as $option) {
            unset($params['driverOptions'][$option]);
        }

        return $params;
    }

    private function isEnabled(string $feature): bool
    {
        return ($_SERVER[$feature] ?? $_ENV[$feature] ?? 'enabled') === 'enabled';
    }
}
