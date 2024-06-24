<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Builder;

use EonX\EasyEncryption\Exceptions\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class AwsCloudHsmSdkOptionsBuilder
{
    private const DEFAULT_AWS_REGION = 'ap-southeast-2';

    public function __construct(
        private string $hsmCaCert,
        private bool $disableKeyAvailabilityCheck = false,
        private ?string $hsmIpAddress = null,
        private ?string $cloudHsmClusterId = null,
        private string $awsRegion = self::DEFAULT_AWS_REGION,
        private ?string $serverClientCertFile = null,
        private ?string $serverClientKeyFile = null,
        private ?array $cloudHsmSdkOptions = null,
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
        if ($isSetServerClientCertFile !== $isSetServerClientKeyFile) {
            throw new InvalidConfigurationException('Both Server Client Cert and Key must be set at the same time');
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
                    throw new InvalidConfigurationException(\sprintf(
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
