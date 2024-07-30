<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Factories;

use EonX\EasyEncryption\Exceptions\CouldNotCreateEncryptionKeyException;
use EonX\EasyEncryption\Exceptions\InvalidEncryptionKeyException;
use EonX\EasyEncryption\Helpers\KeyLength;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use Throwable;

final class DefaultEncryptionKeyFactory implements EncryptionKeyFactoryInterface
{
    public function create(mixed $key): EncryptionKey|EncryptionKeyPair
    {
        try {
            return $this->doCreate($key);
        } catch (Throwable $throwable) {
            throw new CouldNotCreateEncryptionKeyException(
                \sprintf('Could not create encryption key: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidSalt
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doCreate(mixed $key): EncryptionKey|EncryptionKeyPair
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

        throw new InvalidEncryptionKeyException(\sprintf(
            'Invalid key type "%s" given, supports only "array, string"',
            \get_debug_type($key)
        ));
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidSalt
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \SodiumException
     */
    private function doCreateFromArray(array $key): EncryptionKey|EncryptionKeyPair
    {
        if (isset($key[self::OPTION_KEY], $key[self::OPTION_SALT])) {
            return KeyFactory::deriveEncryptionKey(
                $this->getHiddenString((string)$key[self::OPTION_KEY]),
                (string)$key[self::OPTION_SALT]
            );
        }

        if (isset($key[self::OPTION_SECRET_KEY], $key[self::OPTION_PUBLIC_KEY])) {
            return new EncryptionKeyPair(
                new EncryptionSecretKey($this->getHiddenString($key[self::OPTION_SECRET_KEY])),
                new EncryptionPublicKey($this->getHiddenString($key[self::OPTION_PUBLIC_KEY]))
            );
        }

        throw new InvalidEncryptionKeyException('Could not identify key type from given array');
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     */
    private function doCreateFromString(string $key): EncryptionKey|EncryptionKeyPair
    {
        if (KeyLength::isEncryptionKeyLength($key)) {
            return new EncryptionKey($this->getHiddenString($key));
        }

        if (KeyLength::isSecretKeyLength($key)) {
            return new EncryptionKeyPair(new EncryptionSecretKey($this->getHiddenString($key)));
        }

        if (KeyLength::isPublicKeyLength($key)) {
            throw new InvalidEncryptionKeyException('Passing only public key is not supported');
        }

        throw new InvalidEncryptionKeyException('Could not identify key type from given string');
    }

    private function getHiddenString(string $value): HiddenString
    {
        return new HiddenString($value);
    }
}
