<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Configurator;

use Aws\CloudHSMV2\CloudHSMV2Client;
use Aws\Sts\StsClient;
use EonX\EasyEncryption\Builder\AwsCloudHsmSdkOptionsBuilder;
use EonX\EasyEncryption\Exceptions\CouldNotConfigureAwsCloudHsmSdkException;
use EonX\EasyEncryption\Exceptions\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Throwable;

final class AwsCloudHsmSdkConfigurator
{
    private const AWS_CLOUDHSM_API_VERSION = '2017-04-28';

    private const AWS_CLOUDHSM_CLUSTER_TYPE = 'hsm1';

    private const AWS_CLOUDHSM_CONFIGURE_TOOL = '/opt/cloudhsm/bin/configure-pkcs11';

    private const AWS_CLOUDHSM_CONFIG_FILE = '/opt/cloudhsm/etc/cloudhsm-pkcs11.cfg';

    private const AWS_CLOUDHSM_LOG_FILE = '/opt/cloudhsm/run/cloudhsm-pkcs11.log';

    private const AWS_CLOUDHSM_LOG_INTERVAL = 'daily';

    private const AWS_CLOUDHSM_LOG_LEVEL = 'info';

    private const AWS_CLOUDHSM_LOG_TYPE = 'file';

    private const AWS_CLOUDHSM_SERVER_PORT = 2223;

    private const AWS_STS_API_VERSION = '2011-06-15';

    public function __construct(
        private readonly AwsCloudHsmSdkOptionsBuilder $awsCloudHsmSdkOptionsBuilder,
        private readonly ?string $awsRoleArn = null,
        private readonly bool $useCloudHsmConfigureTool = true,
    ) {
    }

    public function configure(): void
    {
        $options = $this->awsCloudHsmSdkOptionsBuilder->build();

        if (
            \array_key_exists('--cluster-id', $options) &&
            $this->useCloudHsmConfigureTool === false &&
            (\class_exists(CloudHSMV2Client::class) === false || \class_exists(StsClient::class) === false)
        ) {
            throw new InvalidConfigurationException(
                'The "aws/aws-sdk-php" package is required to configure CloudHSM without using configure-pkcs11 tool.' .
                ' Install the package, or use the configure-pkcs11 tool, or provide the HSM IP instead of ' .
                'the CloudHSM cluster ID'
            );
        }

        $this->useCloudHsmConfigureTool
            ? $this->configureAwsCloudHsmSdkUsingConfigureTool($options)
            : $this->configureAwsCloudHsmSdkUsingAwsSdk($options);
    }

    private function configureAwsCloudHsmSdkUsingAwsSdk(array $options): void
    {
        $cluster = [
            'client_cert_path' => $options['--server-client-cert-file'],
            'client_key_path' => $options['--server-client-key-file'],
            'hsm_ca_file' => $options['--hsm-ca-cert'],
            'options' => [
                'disable_key_availability_check' => $options['--disable-key-availability-check'],
            ],
        ];
        $servers = [];

        if (\array_key_exists('-a', $options)) {
            $servers[] = [
                'enable' => true,
                'hostname' => $options['-a'],
                'port' => self::AWS_CLOUDHSM_SERVER_PORT,
            ];
        }

        if (\array_key_exists('--cluster-id', $options)) {
            $awsCredentials = null;
            if ($this->awsRoleArn !== null) {
                $stsClient = new StsClient([
                    'profile' => 'default',
                    'region' => $options['--region'],
                    'version' => self::AWS_STS_API_VERSION,
                ]);

                try {
                    $awsResult = $stsClient->assumeRole([
                        'DurationSeconds' => 900,
                        'RoleArn' => $this->awsRoleArn,
                        'RoleSessionName' => 'easy-encryption-cloud-hsm-access',
                    ]);
                    $awsCredentials = $stsClient->createCredentials($awsResult);
                } catch (Throwable $throwable) {
                    throw new CouldNotConfigureAwsCloudHsmSdkException(
                        $throwable->getMessage(),
                        $throwable->getCode(),
                        $throwable
                    );
                }
            }

            $cloudHsmV2ClientOptions = [
                'region' => $options['--region'],
                'version' => self::AWS_CLOUDHSM_API_VERSION,
            ];
            if ($awsCredentials !== null) {
                $cloudHsmV2ClientOptions['credentials'] = $awsCredentials;
            }
            $cloudHsmV2Client = new CloudHSMV2Client($cloudHsmV2ClientOptions);

            try {
                $awsResult = $cloudHsmV2Client->describeClusters([
                    'Filter' => [
                        'clusterIds' => [$options['--cluster-id']],
                    ],
                ]);
            } catch (Throwable $throwable) {
                throw new CouldNotConfigureAwsCloudHsmSdkException(
                    $throwable->getMessage(),
                    $throwable->getCode(),
                    $throwable
                );
            }

            $cloudHsmClusters = (array)$awsResult->get('Clusters');
            $cloudHsmCluster = $cloudHsmClusters[0]
                ?? throw new CouldNotConfigureAwsCloudHsmSdkException(
                    \sprintf('No CloudHSM cluster found for the cluster ID "%s"', $options['--cluster-id'])
                );
            $cloudHsmClusterServers = $cloudHsmCluster['Hsms']
                ?? throw new CouldNotConfigureAwsCloudHsmSdkException(
                    \sprintf('No HSMs found for the cluster ID "%s"', $options['--cluster-id'])
                );

            foreach ((array)$cloudHsmClusterServers as $cloudHsmClusterServer) {
                $servers[] = [
                    'enable' => true,
                    'hostname' => $cloudHsmClusterServer['EniIp']
                        ?? throw new CouldNotConfigureAwsCloudHsmSdkException(
                            'No ENI IP found for the HSM'
                        ),
                    'port' => self::AWS_CLOUDHSM_SERVER_PORT,
                ];
            }

            $cluster['cluster_id'] = $options['--cluster-id'];
        }

        $cluster['servers'] = $servers;
        $config = [
            'clusters' => [
                [
                    'cluster' => $cluster,
                    'type' => self::AWS_CLOUDHSM_CLUSTER_TYPE,
                ],
            ],
            'logging' => [
                'log_file' => $options['--log-file'] ?? self::AWS_CLOUDHSM_LOG_FILE,
                'log_interval' => $options['--log-rotation'] ?? self::AWS_CLOUDHSM_LOG_INTERVAL,
                'log_level' => $options['--log-level'] ?? self::AWS_CLOUDHSM_LOG_LEVEL,
                'log_type' => $options['--log-type'] ?? self::AWS_CLOUDHSM_LOG_TYPE,
            ],
        ];

        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            self::AWS_CLOUDHSM_CONFIG_FILE,
            (string)\json_encode($config, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );
    }

    private function configureAwsCloudHsmSdkUsingConfigureTool(array $options): void
    {
        $cmd = [self::AWS_CLOUDHSM_CONFIGURE_TOOL];

        foreach ($options as $option => $value) {
            if (\is_bool($value) && $value === false) {
                continue;
            }

            $cmd[] = $option;
            $cmd[] = $value;
        }

        try {
            $process = new Process($cmd);
            $process->run();
        } catch (Throwable $throwable) {
            throw new CouldNotConfigureAwsCloudHsmSdkException(
                $throwable->getMessage(),
                $throwable->getCode(),
                $throwable
            );
        }

        if ($process->isSuccessful() === false) {
            throw new CouldNotConfigureAwsCloudHsmSdkException(\sprintf(
                'Output: %s. Error Output: %s',
                $process->getOutput(),
                $process->getErrorOutput()
            ));
        }
    }
}
