<?php

declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use ParagonIE\Halite\Asymmetric\Crypto as AsymmetricCrypto;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\HiddenString as OldHiddenString;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString as NewHiddenString;

final class Encryptor extends AbstractEncryptor
{
    public function __construct(
        private readonly EncryptionKeyFactoryInterface $keyFactory,
        private readonly EncryptionKeyProviderInterface $keyProvider,
        ?string $defaultKeyName = null
    ) {
        parent::__construct($defaultKeyName);
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    protected function doDecrypt(
        string $text,
        null|array|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key,
        bool $raw
    ): string {
        $key = $this->getKey($key, $raw === false);

        if ($key instanceof EncryptionKeyPair) {
            return AsymmetricCrypto::decrypt($text, $key->getSecretKey(), $key->getPublicKey())->getString();
        }

        if ($key instanceof EncryptionKey) {
            return SymmetricCrypto::decrypt($text, $key)->getString();
        }

        throw new InvalidEncryptionKeyException(\sprintf(
            'Expected key instance of %s|%s, %s given',
            EncryptionKey::class,
            EncryptionKeyPair::class,
            \get_class($key)
        ));
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    protected function doEncrypt(
        string $text,
        null|array|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key,
        bool $raw
    ): string {
        $key = $this->getKey($key, $raw === false);
        $text = \class_exists(NewHiddenString::class) ? new NewHiddenString($text) : new OldHiddenString($text);

        if ($key instanceof EncryptionKeyPair) {
            return AsymmetricCrypto::encrypt($text, $key->getSecretKey(), $key->getPublicKey());
        }

        if ($key instanceof EncryptionKey) {
            return SymmetricCrypto::encrypt($text, $key);
        }

        throw new InvalidEncryptionKeyException(\sprintf(
            'Expected key instance of %s|%s, %s given',
            EncryptionKey::class,
            EncryptionKeyPair::class,
            \get_class($key)
        ));
    }

    private function getKey(
        null|array|string|EncryptionKey|EncryptionKeyPair $key = null,
        ?bool $forceKeyName = null
    ): EncryptionKey|EncryptionKeyPair {
        if (($key === null || \is_string($key)) && $this->keyProvider->hasKey($this->getKeyName($key))) {
            return $this->keyProvider->getKey($this->getKeyName($key));
        }

        if ($forceKeyName ?? false) {
            throw new InvalidEncryptionKeyException('Key must be created from its name');
        }

        return $this->keyFactory->create($key);
    }
}
