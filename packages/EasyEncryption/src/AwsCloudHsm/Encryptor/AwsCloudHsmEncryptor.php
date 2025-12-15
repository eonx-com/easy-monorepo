<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\AwsCloudHsm\Encryptor;

use EonX\EasyEncryption\AwsCloudHsm\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\Common\Encryptor\AbstractEncryptor;
use EonX\EasyEncryption\Common\Exception\CouldNotEncryptException;
use EonX\EasyEncryption\Common\Exception\InvalidEncryptionKeyException;
use Pkcs11\GcmParams;
use Pkcs11\Mechanism;
use Pkcs11\Module;
use Pkcs11\Session;
use Throwable;
use UnexpectedValueException;

use const Pkcs11\CKA_LABEL;
use const Pkcs11\CKF_RW_SESSION;
use const Pkcs11\CKM_AES_GCM;
use const Pkcs11\CKM_SHA512_HMAC;
use const Pkcs11\CKM_VENDOR_DEFINED;
use const Pkcs11\CKU_USER;

final class AwsCloudHsmEncryptor extends AbstractEncryptor implements AwsCloudHsmEncryptorInterface
{
    private const CLOUD_HSM_EXTENSION = '/opt/cloudhsm/lib/libcloudhsm_pkcs11.so';

    private const EXCEPTION_DEFAULT_RETRIES = 3;

    private const EXCEPTION_RETRY_MESSAGE = 'CKR_FUNCTION_FAILED';

    private const GCM_TAG_LENGTH = 128;

    private bool $cloudHsmSdkConfigured = false;

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

    public function init(): void
    {
        if ($this->session !== null) {
            return;
        }

        if ($this->cloudHsmSdkConfigured === false) {
            $this->awsCloudHsmSdkConfigurator->configure();
            $this->cloudHsmSdkConfigured = true;
        }

        $this->module ??= new Module(self::CLOUD_HSM_EXTENSION);
        $slots = $this->module->getSlotList();
        $firstSlot = $slots[0] ?? throw new UnexpectedValueException('Slot list is empty');

        $session = $this->module->openSession($firstSlot, CKF_RW_SESSION);
        $session->login(CKU_USER, $this->userPin);

        $this->session = $session;
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
                ->sign(new Mechanism(CKM_SHA512_HMAC, null), $text);

            return \bin2hex((string)$signature);
        });
    }

    protected function doDecrypt(
        string $text,
        null|array|string $key,
        bool $raw,
    ): string {
        $this->validateKey($key);
        $this->init();

        /** @var string|null $keyAsString */
        $keyAsString = $key;

        return $this->execWithRetries(fn (): string => $this
            ->findKey($this->getKeyName($keyAsString))
            ->decrypt($this->getMechanism(), (string)\hex2bin($text)));
    }

    protected function doEncrypt(
        string $text,
        null|array|string $key,
        bool $raw,
    ): string {
        $this->validateKey($key);
        $this->init();

        /** @var string|null $keyAsString */
        $keyAsString = $key;

        $encrypted = $this->execWithRetries(fn (): string => $this
            ->findKey($this->getKeyName($keyAsString))
            ->encrypt($this->getMechanism(), $text));

        return \bin2hex($encrypted);
    }

    /**
     * @throws \Throwable
     */
    private function execWithRetries(callable $callback): string
    {
        $attempt = 0;

        do {
            try {
                return (string)$callback();
            } catch (Throwable $throwable) {
                // Reset PKCS11 session on specific error failure
                if (\str_contains(\strtoupper($throwable->getMessage()), self::EXCEPTION_RETRY_MESSAGE)) {
                    $this->reset();
                }
            }

            $attempt++;
        } while ($attempt < self::EXCEPTION_DEFAULT_RETRIES);

        throw $throwable;
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
            CKA_LABEL => $keyName,
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
            CKM_VENDOR_DEFINED | CKM_AES_GCM,
            new GcmParams('', $this->aad, self::GCM_TAG_LENGTH)
        );
    }

    private function validateKey(array|string|null $key = null): void
    {
        if ($key !== null && (\is_string($key) === false || $key === '')) {
            throw new InvalidEncryptionKeyException(\sprintf(
                'Encryption key must be either null or non-empty string for %s',
                self::class
            ));
        }
    }
}
