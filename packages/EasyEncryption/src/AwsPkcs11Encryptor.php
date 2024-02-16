<?php
declare(strict_types=1);

namespace EonX\EasyEncryption;

use Aws\CloudHSMV2\CloudHSMV2Client;
use Aws\Sts\StsClient;
use EonX\EasyEncryption\Exceptions\CouldNotConfigureAwsCloudHsmSdkException;
use EonX\EasyEncryption\Exceptions\CouldNotEncryptException;
use EonX\EasyEncryption\Exceptions\InvalidConfigurationException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Pkcs11\GcmParams;
use Pkcs11\Mechanism;
use Pkcs11\Module;
use Pkcs11\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Throwable;
use UnexpectedValueException;

final class AwsPkcs11Encryptor extends AbstractEncryptor implements AwsPkcs11EncryptorInterface
{
    private const AWS_CLOUDHSM_API_VERSION = '2017-04-28';

    private const AWS_CLOUDHSM_CLUSTER_TYPE = 'hsm1';

    private const AWS_CLOUDHSM_CONFIGURE_TOOL = '/opt/cloudhsm/bin/configure-pkcs11';

    private const AWS_CLOUDHSM_CONFIG_FILE = '/opt/cloudhsm/etc/cloudhsm-pkcs11.cfg';

    private const AWS_CLOUDHSM_EXTENSION = '/opt/cloudhsm/lib/libcloudhsm_pkcs11.so';

    private const AWS_CLOUDHSM_LOG_FILE = '/opt/cloudhsm/run/cloudhsm-pkcs11.log';

    private const AWS_CLOUDHSM_LOG_INTERVAL = 'daily';

    private const AWS_CLOUDHSM_LOG_LEVEL = 'info';

    private const AWS_CLOUDHSM_LOG_TYPE = 'file';

    private const AWS_CLOUDHSM_SERVER_PORT = 2223;

    private const AWS_GCM_TAG_LENGTH = 128;

    private const AWS_STS_API_VERSION = '2011-06-15';

    private bool $awsCloudHsmConfigured = false;

    /**
     * @var \Pkcs11\Key[]
     */
    private array $keys = [];

    private ?Module $module = null;

    private ?Session $session = null;

    public function __construct(
        private readonly string $userPin,
        private readonly string $hsmCaCert,
        private readonly bool $disableKeyAvailabilityCheck = false,
        private readonly ?string $hsmIpAddress = null,
        private readonly ?string $cloudHsmClusterId = null,
        private readonly string $awsRegion = 'ap-southeast-2',
        private readonly ?string $awsRoleArn = null,
        private readonly string $aad = '',
        private readonly ?string $serverClientCertFile = null,
        private readonly ?string $serverClientKeyFile = null,
        private readonly ?array $cloudHsmConfigureToolOptions = null,
        private readonly bool $useCloudHsmConfigureTool = true,
        ?string $defaultKeyName = null,
    ) {
        parent::__construct($defaultKeyName);
    }

    public function reset(): void
    {
        if ($this->session === null) {
            return;
        }

        $this->keys = [];
        $this->session = null;
        $this->module = null;
    }

    public function sign(string $text, ?string $keyName = null): string
    {
        return $this->execSafely(CouldNotEncryptException::class, function () use ($text, $keyName): string {
            $keyName = $this->getKeyName($keyName);
            $this->validateKey($keyName);
            $this->init();

            return $this
                ->findKey($keyName)
                ->sign(new Mechanism(\Pkcs11\CKM_SHA512_HMAC, null), $text);
        });
    }

    protected function doDecrypt(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key,
        bool $raw,
    ): string {
        $this->validateKey($key);
        $this->init();

        /** @var string|null $keyAsString */
        $keyAsString = $key;

        return $this
            ->findKey($this->getKeyName($keyAsString))
            ->decrypt($this->getMechanism(), (string)\hex2bin($text));
    }

    protected function doEncrypt(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key,
        bool $raw,
    ): string {
        $this->validateKey($key);
        $this->init();

        /** @var string|null $keyAsString */
        $keyAsString = $key;

        $encrypted = $this
            ->findKey($this->getKeyName($keyAsString))
            ->encrypt($this->getMechanism(), $text);

        return \bin2hex((string)$encrypted);
    }

    private function configureAwsCloudHsmSdk(): void
    {
        $filesystem = new Filesystem();
        $isSetHsmIpAddress = $this->isNonEmptyString($this->hsmIpAddress);
        $isSetCloudHsmClusterId = $this->isNonEmptyString($this->cloudHsmClusterId);
        $isSetServerClientCertFile = $this->isNonEmptyString($this->serverClientCertFile);
        $isSetServerClientKeyFile = $this->isNonEmptyString($this->serverClientKeyFile);

        if ($filesystem->exists($this->hsmCaCert) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'Given CA Cert filename "%s" does not exist',
                $this->hsmCaCert
            ));
        }
        if ($isSetHsmIpAddress === false && $isSetCloudHsmClusterId === false) {
            throw new InvalidConfigurationException(
                'At least HSM IP address or CloudHSM cluster id has to be set'
            );
        }
        if ($isSetHsmIpAddress && $isSetCloudHsmClusterId) {
            throw new InvalidConfigurationException(
                'Both HSM IP address and CloudHSM cluster id options cannot be set at the same time'
            );
        }
        if (
            $isSetCloudHsmClusterId &&
            $this->useCloudHsmConfigureTool === false &&
            (\class_exists(CloudHSMV2Client::class) === false || \class_exists(StsClient::class) === false)
        ) {
            throw new InvalidConfigurationException(
                'The "aws/aws-sdk-php" package is required to configure CloudHSM without using configure-pkcs11 tool.' .
                ' Install the package, or use the configure-pkcs11 tool, or provide the HSM IP instead of ' .
                'the CloudHSM cluster ID'
            );
        }
        if ($isSetServerClientCertFile !== $isSetServerClientKeyFile) {
            throw new InvalidConfigurationException('Both Server Client Cert and Key must be set at the same time');
        }

        $options = $this->cloudHsmConfigureToolOptions ?? [];

        if ($isSetHsmIpAddress) {
            $options['-a'] = $this->hsmIpAddress;
        }
        if ($isSetCloudHsmClusterId) {
            $options['--cluster-id'] = $this->cloudHsmClusterId;
        }

        $options['--hsm-ca-cert'] = $this->hsmCaCert;
        $options['--region'] = $this->awsRegion;

        if ($isSetServerClientCertFile && $isSetServerClientKeyFile) {
            $sslFiles = [
                '--server-client-cert-file' => $this->serverClientCertFile,
                '--server-client-key-file' => $this->serverClientKeyFile,
            ];

            /** @var string $filename */
            foreach ($sslFiles as $option => $filename) {
                if ($filesystem->exists($filename) === false) {
                    throw new InvalidConfigurationException(\sprintf(
                        'Filename "%s" for option "%s" does not exist',
                        $filename,
                        $option
                    ));
                }

                $options[$option] = $filename;
            }
        }

        if ($this->disableKeyAvailabilityCheck) {
            $options[] = '--disable-key-availability-check';
        }

        if ($this->useCloudHsmConfigureTool) {
            $this->configureAwsCloudHsmSdkUsingConfigureTool($options);

            return;
        }

        $this->configureAwsCloudHsmSdkUsingAwsSdk($options);
    }

    private function configureAwsCloudHsmSdkUsingAwsSdk(array $options): void
    {
        $cluster = [
            'client_cert_path' => $options['--server-client-cert-file'],
            'client_key_path' => $options['--server-client-key-file'],
            'hsm_ca_file' => $options['--hsm-ca-cert'],
            'options' => [
                'disable_key_availability_check' => \in_array('--disable-key-availability-check', $options, true),
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

            $cloudHSMV2ClientOptions = [
                'region' => $options['--region'],
                'version' => self::AWS_CLOUDHSM_API_VERSION,
            ];
            if ($awsCredentials !== null) {
                $cloudHSMV2ClientOptions['credentials'] = $awsCredentials;
            }
            $cloudHSMV2Client = new CloudHSMV2Client($cloudHSMV2ClientOptions);

            try {
                $awsResult = $cloudHSMV2Client->describeClusters([
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
            \json_encode($config, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );
    }

    private function configureAwsCloudHsmSdkUsingConfigureTool(array $options): void
    {
        $cmd = [self::AWS_CLOUDHSM_CONFIGURE_TOOL];

        foreach ($options as $option => $value) {
            \is_string($option)
                ? \array_push($cmd, $option, $value)
                : \array_push($cmd, $value);
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

    /**
     * @return \Pkcs11\Key
     */
    private function findKey(?string $keyName = null): object
    {
        $keyName = $this->getKeyName($keyName);

        if (isset($this->keys[$keyName])) {
            return $this->keys[$keyName];
        }

        $objects = $this->session?->findObjects([
            \Pkcs11\CKA_LABEL => $keyName,
        ]) ?? [];

        if (\is_array($objects) && \count($objects) > 0) {
            return $this->keys[$keyName] = $objects[0];
        }

        throw new InvalidEncryptionKeyException(\sprintf(
            'No key handle found for label "%s"',
            $keyName
        ));
    }

    private function getMechanism(): Mechanism
    {
        return new Mechanism(
            \Pkcs11\CKM_VENDOR_DEFINED | \Pkcs11\CKM_AES_GCM,
            new GcmParams('', $this->aad, self::AWS_GCM_TAG_LENGTH)
        );
    }

    private function init(): void
    {
        if ($this->session !== null) {
            return;
        }

        if ($this->awsCloudHsmConfigured === false) {
            $this->configureAwsCloudHsmSdk();
            $this->awsCloudHsmConfigured = true;
        }

        $this->module ??= new Module(self::AWS_CLOUDHSM_EXTENSION);
        $slots = $this->module->getSlotList();
        $firstSlot = $slots[0] ?? throw new UnexpectedValueException('Slot list is empty');

        $session = $this->module->openSession($firstSlot, \Pkcs11\CKF_RW_SESSION);
        $session->login(\Pkcs11\CKU_USER, $this->userPin);

        $this->session = $session;
    }

    private function isNonEmptyString(mixed $string): bool
    {
        return \is_string($string) && $string !== '';
    }

    private function validateKey(EncryptionKey|EncryptionKeyPair|array|string|null $key = null): void
    {
        if ($key !== null && $this->isNonEmptyString($key) === false) {
            throw new InvalidEncryptionKeyException(\sprintf(
                'Encryption key must be either null or string for %s',
                self::class
            ));
        }
    }
}
