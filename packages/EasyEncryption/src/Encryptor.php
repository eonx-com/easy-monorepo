<?php

declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Exceptions\CouldNotDecryptException;
use EonX\EasyEncryption\Exceptions\CouldNotEncryptException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\DecryptedStringInterface;
use EonX\EasyEncryption\Interfaces\EasyEncryptionExceptionInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\ValueObjects\DecryptedString;
use ParagonIE\ConstantTime\Encoding;
use ParagonIE\Halite\Asymmetric\Crypto as AsymmetricCrypto;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\HiddenString as OldHiddenString;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString as NewHiddenString;
use Throwable;

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

    public function decrypt(string $text): DecryptedStringInterface
    {
        $toDecrypt = $this->execSafely(CouldNotDecryptException::class, function () use ($text): array {
            $toDecryptArray = \json_decode(Encoding::base64Decode($text), true);

            return \is_array($toDecryptArray) ? $toDecryptArray : [];
        });

        if (isset($toDecrypt[self::ENCRYPTED_KEY_NAME], $toDecrypt[self::ENCRYPTED_KEY_VALUE]) === false) {
            throw new CouldNotDecryptException('Given encrypted text has invalid structure');
        }

        return $this->execSafely(
            CouldNotDecryptException::class,
            function () use ($toDecrypt): DecryptedStringInterface {
                $keyName = $toDecrypt[self::ENCRYPTED_KEY_NAME];

                return new DecryptedString(
                    $this->decryptRaw($toDecrypt[self::ENCRYPTED_KEY_VALUE], $this->getKey($keyName, true)),
                    $keyName
                );
            }
        );
    }

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function decryptRaw(string $text, $key = null): string
    {
        return $this->execSafely(CouldNotDecryptException::class, function () use ($text, $key): string {
            return $this->doDecrypt($text, $this->getKey($key));
        });
    }

    public function encrypt(string $text, ?string $keyName = null): string
    {
        return $this->execSafely(CouldNotEncryptException::class, function () use ($text, $keyName): string {
            $keyName = $this->getKeyName($keyName);

            return Encoding::base64Encode((string)\json_encode([
                self::ENCRYPTED_KEY_NAME => $keyName,
                self::ENCRYPTED_KEY_VALUE => $this->encryptRaw($text, $this->getKey($keyName, true)),
            ]));
        });
    }

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function encryptRaw(string $text, $key = null): string
    {
        return $this->execSafely(CouldNotEncryptException::class, function () use ($text, $key): string {
            return $this->doEncrypt($text, $this->getKey($key));
        });
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

    /**
     * @phpstan-param class-string<T> $throwableClass
     *
     * @phpstan-template T of \Throwable
     *
     * @throws T
     */
    private function execSafely(string $throwableClass, callable $func): mixed
    {
        try {
            return $func();
        } catch (Throwable $throwable) {
            if ($throwable instanceof EasyEncryptionExceptionInterface) {
                throw $throwable;
            }

            throw new $throwableClass($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    private function getKey($key = null, ?bool $forceKeyName = null)
    {
        if (($key === null || \is_string($key)) && $this->keyProvider->hasKey($this->getKeyName($key))) {
            return $this->keyProvider->getKey($key ?? $this->defaultKeyName);
        }

        if ($forceKeyName ?? false) {
            throw new InvalidEncryptionKeyException('Key must be created from its name');
        }

        return $this->keyFactory->create($key);
    }

    private function getKeyName(?string $keyName = null): string
    {
        return $keyName ?? $this->defaultKeyName;
    }
}
