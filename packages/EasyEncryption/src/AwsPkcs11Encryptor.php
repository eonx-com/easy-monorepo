<?php
declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\Exceptions\CouldNotEncryptException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Pkcs11\GcmParams;
use Pkcs11\Mechanism;
use Pkcs11\Module;
use Pkcs11\Session;
use UnexpectedValueException;

final class AwsPkcs11Encryptor extends AbstractEncryptor implements AwsPkcs11EncryptorInterface
{
    private const AWS_CLOUDHSM_EXTENSION = '/opt/cloudhsm/lib/libcloudhsm_pkcs11.so';

    private const AWS_GCM_TAG_LENGTH = 128;

    private bool $awsCloudHsmSdkConfigured = false;

    /**
     * @var \Pkcs11\Key[]
     */
    private array $keys = [];

    private ?Module $module = null;

    private ?Session $session = null;

    public function __construct(
        private readonly string $userPin,
        private readonly AwsCloudHsmSdkConfigurator $awsCloudHsmSdkConfigurator,
        private readonly string $aad = '',
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

            $signature = $this
                ->findKey($keyName)
                ->sign(new Mechanism(\Pkcs11\CKM_SHA512_HMAC, null), $text);

            return \bin2hex((string)$signature);
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

        if ($this->awsCloudHsmSdkConfigured === false) {
            $this->awsCloudHsmSdkConfigurator->configure();
            $this->awsCloudHsmSdkConfigured = true;
        }

        $this->module ??= new Module(self::AWS_CLOUDHSM_EXTENSION);
        $slots = $this->module->getSlotList();
        $firstSlot = $slots[0] ?? throw new UnexpectedValueException('Slot list is empty');

        $session = $this->module->openSession($firstSlot, \Pkcs11\CKF_RW_SESSION);
        $session->login(\Pkcs11\CKU_USER, $this->userPin);

        $this->session = $session;
    }

    private function validateKey(EncryptionKey|EncryptionKeyPair|array|string|null $key = null): void
    {
        if ($key !== null && (\is_string($key) === false || $key === '')) {
            throw new InvalidEncryptionKeyException(\sprintf(
                'Encryption key must be either null or non-empty string for %s',
                self::class
            ));
        }
    }
}
