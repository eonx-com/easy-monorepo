<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\AwsCloudHsm\Configurator;

use Aws\CloudHSMV2\CloudHSMV2Client;
use Aws\Sts\StsClient;
use EonX\EasyEncryption\AwsCloudHsm\Builder\AwsCloudHsmSdkOptionsBuilder;
use EonX\EasyEncryption\AwsCloudHsm\Exception\AwsCloudHsmCouldNotConfigureSdkException;
use EonX\EasyEncryption\AwsCloudHsm\Exception\AwsCloudHsmInvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Throwable;

final readonly class AwsCloudHsmSdkConfigurator
{
    private const API_VERSION = '2017-04-28';

    private const CLUSTER_TYPE = 'hsm1';

    private const CONFIGURE_TOOL = '/opt/cloudhsm/bin/configure-pkcs11';

    private const CONFIG_FILE = '/opt/cloudhsm/etc/cloudhsm-pkcs11.cfg';

    private const LOG_FILE = '/opt/cloudhsm/run/cloudhsm-pkcs11.log';

    private const LOG_INTERVAL = 'daily';

    private const LOG_LEVEL = 'info';

    private const LOG_TYPE = 'file';

    private const SERVER_PORT = 2223;

    private const STS_API_VERSION = '2011-06-15';

    public function __construct(
        private AwsCloudHsmSdkOptionsBuilder $awsCloudHsmSdkOptionsBuilder,
        private ?string $roleArn = null,
        private bool $useConfigureTool = true,
    ) {
    }

    public function configure(): void
    {
        $options = $this->awsCloudHsmSdkOptionsBuilder->build();

        if (
            \array_key_exists('--cluster-id', $options) &&
            $this->useConfigureTool === false &&
            (\class_exists(CloudHSMV2Client::class) === false || \class_exists(StsClient::class) === false)
        ) {
            throw new AwsCloudHsmInvalidConfigurationException(
                'The "aws/aws-sdk-php" package is required to configure CloudHSM without using configure-pkcs11 tool.' .
                ' Install the package, or use the configure-pkcs11 tool, or provide the HSM IP instead of ' .
                'the CloudHSM cluster ID'
            );
        }

        $this->useConfigureTool
            ? $this->configureAwsCloudHsmSdkUsingConfigureTool($options)
            : $this->configureAwsCloudHsmSdkUsingAwsSdk($options);
    }

    private function configureAwsCloudHsmSdkUsingAwsSdk(array $options): void
    {
        $cluster = [
            'hsm_ca_file' => $options['--hsm-ca-cert'],
            'options' => [
                'disable_key_availability_check' => $options['--disable-key-availability-check'],
            ],
        ];

        if (isset($options['--server-client-cert-file']) && isset($options['--server-client-key-file'])) {
            $cluster['client_cert_path'] = $options['--server-client-cert-file'];
            $cluster['client_key_path'] = $options['--server-client-key-file'];
        }

        $servers = [];

        if (isset($options['-a'])) {
            $hsmIpAddresses = \explode(' ', (string)$options['-a']);

            foreach ($hsmIpAddresses as $hsmIpAddress) {
                $servers[] = [
                    'enable' => true,
                    'hostname' => $hsmIpAddress,
                    'port' => self::SERVER_PORT,
                ];
            }
        }

        if (isset($options['--cluster-id'])) {
            $awsCredentials = null;
            if ($this->roleArn !== null) {
                $stsClient = new StsClient([
                    'profile' => 'default',
                    'region' => $options['--region'],
                    'version' => self::STS_API_VERSION,
                ]);

                try {
                    $awsResult = $stsClient->assumeRole([
                        'DurationSeconds' => 900,
                        'RoleArn' => $this->roleArn,
                        'RoleSessionName' => 'easy-encryption-cloud-hsm-access',
                    ]);
                    $awsCredentials = $stsClient->createCredentials($awsResult);
                } catch (Throwable $throwable) {
                    throw new AwsCloudHsmCouldNotConfigureSdkException(
                        $throwable->getMessage(),
                        $throwable->getCode(),
                        $throwable
                    );
                }
            }

            $cloudHsmV2ClientOptions = [
                'region' => $options['--region'],
                'version' => self::API_VERSION,
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
                throw new AwsCloudHsmCouldNotConfigureSdkException(
                    $throwable->getMessage(),
                    $throwable->getCode(),
                    $throwable
                );
            }

            $cloudHsmClusters = (array)$awsResult->get('Clusters');
            /** @var array $cloudHsmCluster */
            $cloudHsmCluster = $cloudHsmClusters[0]
                ?? throw new AwsCloudHsmCouldNotConfigureSdkException(
                    \sprintf('No CloudHSM cluster found for the cluster ID "%s"', $options['--cluster-id'])
                );
            $cloudHsmClusterServers = $cloudHsmCluster['Hsms']
                ?? throw new AwsCloudHsmCouldNotConfigureSdkException(
                    \sprintf('No HSMs found for the cluster ID "%s"', $options['--cluster-id'])
                );

            /** @var array $cloudHsmClusterServer */
            foreach ((array)$cloudHsmClusterServers as $cloudHsmClusterServer) {
                $servers[] = [
                    'enable' => true,
                    'hostname' => $cloudHsmClusterServer['EniIp']
                        ?? throw new AwsCloudHsmCouldNotConfigureSdkException(
                            'No ENI IP found for the HSM'
                        ),
                    'port' => self::SERVER_PORT,
                ];
            }

            $cluster['cluster_id'] = $options['--cluster-id'];
        }

        $cluster['servers'] = $servers;
        $config = [
            'clusters' => [
                [
                    'cluster' => $cluster,
                    'type' => self::CLUSTER_TYPE,
                ],
            ],
            'logging' => [
                'log_file' => $options['--log-file'] ?? self::LOG_FILE,
                'log_interval' => $options['--log-rotation'] ?? self::LOG_INTERVAL,
                'log_level' => $options['--log-level'] ?? self::LOG_LEVEL,
                'log_type' => $options['--log-type'] ?? self::LOG_TYPE,
            ],
        ];

        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            self::CONFIG_FILE,
            (string)\json_encode($config, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );
    }

    private function configureAwsCloudHsmSdkUsingConfigureTool(array $options): void
    {
        $cmd = [self::CONFIGURE_TOOL];

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
            throw new AwsCloudHsmCouldNotConfigureSdkException(
                $throwable->getMessage(),
                $throwable->getCode(),
                $throwable
            );
        }

        if ($process->isSuccessful() === false) {
            throw new AwsCloudHsmCouldNotConfigureSdkException(\sprintf(
                'Output: %s. Error Output: %s',
                $process->getOutput(),
                $process->getErrorOutput()
            ));
        }
    }
}
