<?php

declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Exceptions\CouldNotDecryptException;
use EonX\EasyEncryption\Exceptions\CouldNotEncryptException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EasyEncryptionExceptionInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use ParagonIE\Halite\Asymmetric\Crypto as AsymmetricCrypto;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class Encryptor implements EncryptorInterface
{
    /**
     * @var string
     */
    private $defaultKeyName;

    /**
     * @var \EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface
     */
    private $keyFactory;

    /**
     * @var \EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface
     */
    private $keyProvider;

    public function __construct(
        EncryptionKeyFactoryInterface $keyFactory,
        EncryptionKeyProviderInterface $keyProvider,
        ?string $defaultKeyName = null
    ) {
        $this->keyFactory = $keyFactory;
        $this->keyProvider = $keyProvider;
        $this->defaultKeyName = $defaultKeyName ?? self::DEFAULT_KEY_NAME;
    }

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function decrypt(string $text, $key = null): string
    {
        try {
            return $this->doDecrypt($text, $this->getKey($key));
        } catch (\Throwable $throwable) {
            if ($throwable instanceof EasyEncryptionExceptionInterface) {
                throw $throwable;
            }

            throw new CouldNotDecryptException(
                \sprintf('Could not decrypt: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function encrypt(string $text, $key = null): string
    {
        try {
            return $this->doEncrypt($text, $this->getKey($key));
        } catch (\Throwable $throwable) {
            if ($throwable instanceof EasyEncryptionExceptionInterface) {
                throw $throwable;
            }

            throw new CouldNotEncryptException(
                \sprintf('Could not encrypt: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    /**
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doDecrypt(string $text, object $key): string
    {
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
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doEncrypt(string $text, object $key): string
    {
        $text = new HiddenString($text);

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

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    private function getKey($key = null)
    {
        if (($key === null || \is_string($key)) && $this->keyProvider->hasKey($key ?? $this->defaultKeyName)) {
            return $this->keyProvider->getKey($key ?? $this->defaultKeyName);
        }

        return $this->keyFactory->create($key);
    }
}
