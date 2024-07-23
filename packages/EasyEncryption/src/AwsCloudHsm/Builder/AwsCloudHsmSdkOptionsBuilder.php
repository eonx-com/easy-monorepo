<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\AwsCloudHsm\Builder;

use EonX\EasyEncryption\AwsCloudHsm\Exception\AwsCloudHsmInvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class AwsCloudHsmSdkOptionsBuilder
{
    private const DEFAULT_REGION = 'ap-southeast-2';

    public function __construct(
        private string $caCertFile,
        private bool $disableKeyAvailabilityCheck = false,
        private ?string $ipAddress = null,
        private ?string $clusterId = null,
        private string $region = self::DEFAULT_REGION,
        private ?string $serverClientCertFile = null,
        private ?string $serverClientKeyFile = null,
        private ?array $sdkOptions = null,
    ) {
    }

    public function build(): array
    {
        $filesystem = new Filesystem();
        $isSetHsmIpAddress = $this->isNonEmptyString($this->ipAddress);
        $isSetCloudHsmClusterId = $this->isNonEmptyString($this->clusterId);
        $isSetServerClientCertFile = $this->isNonEmptyString($this->serverClientCertFile);
        $isSetServerClientKeyFile = $this->isNonEmptyString($this->serverClientKeyFile);

        if ($filesystem->exists($this->caCertFile) === false) {
            throw new AwsCloudHsmInvalidConfigurationException(\sprintf(
                'Given CA Cert filename "%s" does not exist',
                $this->caCertFile
            ));
        }
        if ($isSetHsmIpAddress === false && $isSetCloudHsmClusterId === false) {
            throw new AwsCloudHsmInvalidConfigurationException(
                'At least HSM IP address or CloudHSM cluster id has to be set'
            );
        }
        if ($isSetHsmIpAddress && $isSetCloudHsmClusterId) {
            throw new AwsCloudHsmInvalidConfigurationException(
                'Both HSM IP address and CloudHSM cluster id options cannot be set at the same time'
            );
        }
        if ($isSetServerClientCertFile !== $isSetServerClientKeyFile) {
            throw new AwsCloudHsmInvalidConfigurationException(
                'Both Server Client Cert and Key must be set at the same time'
            );
        }

        $options = $this->sdkOptions ?? [];

        if ($isSetHsmIpAddress) {
            $options['-a'] = $this->ipAddress;
        }

        if ($isSetCloudHsmClusterId) {
            $options['--cluster-id'] = $this->clusterId;
        }

        $options['--hsm-ca-cert'] = $this->caCertFile;
        $options['--region'] = $this->region;

        if ($isSetServerClientCertFile && $isSetServerClientKeyFile) {
            $sslFiles = [
                '--server-client-cert-file' => $this->serverClientCertFile,
                '--server-client-key-file' => $this->serverClientKeyFile,
            ];

            /** @var string $filename */
            foreach ($sslFiles as $option => $filename) {
                if ($filesystem->exists($filename) === false) {
                    throw new AwsCloudHsmInvalidConfigurationException(\sprintf(
                        'Filename "%s" for option "%s" does not exist',
                        $filename,
                        $option
                    ));
                }

                $options[$option] = $filename;
            }
        }

        $options['--disable-key-availability-check'] = $this->disableKeyAvailabilityCheck;

        return $options;
    }

    private function isNonEmptyString(mixed $string): bool
    {
        return \is_string($string) && $string !== '';
    }
}
