<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\AwsRds\Provider;

use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Credentials\CredentialsInterface;
use Aws\Sts\StsClient;
use EonX\EasyDoctrine\AwsRds\Enum\AwsRdsOption;
use Psr\Log\LoggerInterface;

final readonly class AwsRdsAuthTokenCredentialsProvider implements AwsRdsAuthTokenCredentialsProviderInterface
{
    public function __construct(
        private ?string $assumeRoleArn = null,
        private ?string $assumeRoleDurationSeconds = null,
        private ?string $assumeRoleRegion = null,
        private ?string $assumeRoleSessionName = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function provide(string $awsRegion, array $params): callable|CredentialsInterface
    {
        $driverOptions = $params['driverOptions'] ?? [];
        $assumeRoleArn = $driverOptions[AwsRdsOption::AssumeRoleArn->value] ?? $this->assumeRoleArn;

        // Support __not_implemented__ value as env vars do not always support null or empty values
        if (\is_string($assumeRoleArn) && $assumeRoleArn !== '' && $assumeRoleArn !== '__not_implemented__') {
            $assumeRoleDurationSeconds = (int)$driverOptions[AwsRdsOption::AssumeRoleDurationSeconds->value]
                ?? $this->assumeRoleDurationSeconds
                ?? 900;

            $assumeRoleRegion = (string)$driverOptions[AwsRdsOption::AssumeRoleRegion->value]
                ?? $this->assumeRoleRegion
                ?? $awsRegion;

            $assumeRoleSessionName = (string)$driverOptions[AwsRdsOption::AssumeRoleSessionName->value]
                ?? $this->assumeRoleSessionName
                ?? \hash('xxh128', $assumeRoleArn . $assumeRoleRegion);

            $this->logger?->debug('Using AssumeRoleCredentialProvider for AWS RDS IAM auth', [
                'durationSeconds' => $assumeRoleDurationSeconds,
                'region' => $assumeRoleRegion,
                'roleArn' => $assumeRoleArn,
                'sessionName' => $assumeRoleSessionName
            ]);

            return new AssumeRoleCredentialProvider([
                'assume_role_params' => [
                    'DurationSeconds' => $assumeRoleDurationSeconds,
                    'RoleArn' => $assumeRoleArn,
                    'RoleSessionName' => $assumeRoleSessionName,
                ],
                'client' => new StsClient([
                    'region' => $assumeRoleRegion,
                    'version' => 'latest',
                ]),
            ]);
        }

        return CredentialProvider::defaultProvider();
    }
}
