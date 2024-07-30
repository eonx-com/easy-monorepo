<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Builders;

use EonX\EasyEncryption\Exceptions\AwsCloudHsmInvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final class AwsCloudHsmSdkOptionsBuilder
{
    private const DEFAULT_AWS_REGION = 'ap-southeast-2';

    public function __construct(
        private readonly string $hsmCaCert,
        private readonly bool $disableKeyAvailabilityCheck = false,
        private readonly ?string $hsmIpAddress = null,
        private readonly ?string $cloudHsmClusterId = null,
        private readonly string $awsRegion = self::DEFAULT_AWS_REGION,
        private readonly ?string $serverClientCertFile = null,
        private readonly ?string $serverClientKeyFile = null,
        private readonly ?array $cloudHsmSdkOptions = null,
    ) {
    }

    public function build(): array
    {
        $filesystem = new Filesystem();
        $isSetHsmIpAddress = $this->isNonEmptyString($this->hsmIpAddress);
        $isSetCloudHsmClusterId = $this->isNonEmptyString($this->cloudHsmClusterId);
        $isSetServerClientCertFile = $this->isNonEmptyString($this->serverClientCertFile);
        $isSetServerClientKeyFile = $this->isNonEmptyString($this->serverClientKeyFile);

        if ($filesystem->exists($this->hsmCaCert) === false) {
            throw new AwsCloudHsmInvalidConfigurationException(\sprintf(
                'Given CA Cert filename "%s" does not exist',
                $this->hsmCaCert
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

        $options = $this->cloudHsmSdkOptions ?? [];

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
