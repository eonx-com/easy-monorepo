<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Factories;

use EonX\EasyEncryption\Exceptions\CouldNotCreateEncryptionKeyException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class DefaultEncryptionKeyFactory implements EncryptionKeyFactoryInterface
{
    /**
     * @param mixed $key
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function create($key)
    {
        try {
            return $this->doCreate($key);
        } catch (\Throwable $throwable) {
            throw new CouldNotCreateEncryptionKeyException(
                \sprintf('Could not create encryption key: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    /**
     * @param mixed $key
     *
     * @return \ParagonIE\Halite\EncryptionKeyPair|\ParagonIE\Halite\Symmetric\EncryptionKey
     *
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidSalt
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doCreate($key)
    {
        if ($key instanceof EncryptionKey || $key instanceof EncryptionKeyPair) {
            return $key;
        }

        if (\is_string($key)) {
            return $this->doCreateFromString($key);
        }

        if (\is_array($key)) {
            return $this->doCreateFromArray($key);
        }

        throw new InvalidEncryptionKeyException(
            'Invalid key type "%s" given, supports only "array, string"',
            \is_object($key) ? \get_class($key) : \gettype($key)
        );
    }

    /**
     * @param mixed[] $key
     *
     * @return \ParagonIE\Halite\EncryptionKeyPair|\ParagonIE\Halite\Symmetric\EncryptionKey
     *
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidSalt
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doCreateFromArray(array $key)
    {
        if (isset($key[self::OPTION_KEY], $key[self::OPTION_SALT])) {
            return KeyFactory::deriveEncryptionKey(
                new HiddenString((string)$key[self::OPTION_KEY]),
                (string)$key[self::OPTION_SALT]
            );
        }

        throw new InvalidEncryptionKeyException('Could not identify key type from given array');
    }

    /**
     * @return \ParagonIE\Halite\EncryptionKeyPair|\ParagonIE\Halite\Symmetric\EncryptionKey
     *
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \SodiumException
     */
    private function doCreateFromString(string $key)
    {
        $binaryLength = Binary::safeStrlen($key);

        if ($binaryLength === \SODIUM_CRYPTO_STREAM_KEYBYTES) {
            return new EncryptionKey(new HiddenString($key));
        }

        if ($binaryLength === \SODIUM_CRYPTO_BOX_SECRETKEYBYTES) {
            return new EncryptionKeyPair(new EncryptionSecretKey(new HiddenString($key)));
        }

        if ($binaryLength === \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
            throw new InvalidEncryptionKeyException('Passing only public key is not supported');
        }

        throw new InvalidEncryptionKeyException('Could not identify key type from given string');
    }
}
