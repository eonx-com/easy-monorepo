<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Builder;

use EonX\EasyEncryption\Exceptions\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final class AwsCloudHsmSdkOptionsBuilder
{
    private const DEFAULT_AWS_REGION = 'ap-southeast-2';

    public function __construct(
        private readonly string $hsmCaCert,
        private readonly array $hsmIpAddresses,
        private readonly bool $disableKeyAvailabilityCheck = false,
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
        $isSetHsmIpAddresses = \count($this->hsmIpAddresses) > 0;
        $isSetCloudHsmClusterId = $this->isNonEmptyString($this->cloudHsmClusterId);
        $isSetServerClientCertFile = $this->isNonEmptyString($this->serverClientCertFile);
        $isSetServerClientKeyFile = $this->isNonEmptyString($this->serverClientKeyFile);

        if ($filesystem->exists($this->hsmCaCert) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'Given CA Cert filename "%s" does not exist',
                $this->hsmCaCert
            ));
        }
        if ($isSetHsmIpAddresses === false && $isSetCloudHsmClusterId === false) {
            throw new InvalidConfigurationException(
                'At least HSM IP addresses or CloudHSM cluster id has to be set'
            );
        }
        if ($isSetHsmIpAddresses && $isSetCloudHsmClusterId) {
            throw new InvalidConfigurationException(
                'Both HSM IP addresses and CloudHSM cluster id options cannot be set at the same time'
            );
        }
        if ($isSetServerClientCertFile !== $isSetServerClientKeyFile) {
            throw new InvalidConfigurationException('Both Server Client Cert and Key must be set at the same time');
        }

        $options = $this->cloudHsmSdkOptions ?? [];

        if ($isSetHsmIpAddresses) {
            $options['-a'] = \implode(' ', $this->hsmIpAddresses);
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
