<?php
declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Exceptions\CouldNotConfigureAwsCloudHsmSdkException;
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
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

final class AwsPkcs11Encryptor extends AbstractEncryptor implements AwsPkcs11EncryptorInterface, ResetInterface
{
    private const AWS_CLOUDHSM_CONFIGURE_TOOL = '/opt/cloudhsm/bin/configure-pkcs11';

    private const AWS_CLOUDHSM_EXTENSION = '/opt/cloudhsm/lib/libcloudhsm_pkcs11.so';

    private const AWS_GCM_TAG_LENGTH = 128;

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
        private readonly string $aad = '',
        private readonly ?string $serverClientCertFile = null,
        private readonly ?string $serverClientKeyFile = null,
        private readonly ?array $awsCloudHsmSdkOptions = null,
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
        $this->session->logout();
        $this->module?->C_CloseSession($this->session);
        $this->session = null;
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
            ($isSetServerClientCertFile && $isSetServerClientKeyFile === false) ||
            ($isSetServerClientCertFile === false && $isSetServerClientKeyFile)
        ) {
            throw new InvalidConfigurationException('Both Server Client Cert and Key must be set at the same time');
        }

        $options = $this->awsCloudHsmSdkOptions ?? [];

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

        $session = $this->module->openSession($this->module->getSlotList()[0], \Pkcs11\CKF_RW_SESSION);
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
