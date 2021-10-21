<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Utils;

use ParagonIE\ConstantTime\Binary;

final class KeyLength
{
    public static function getEncryptionKeyLength(): int
    {
        return \SODIUM_CRYPTO_STREAM_KEYBYTES;
    }

    public static function getPublicKeyLength(): int
    {
        return \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES;
    }

    public static function getSaltLength(): int
    {
        return \SODIUM_CRYPTO_PWHASH_SALTBYTES;
    }

    public static function getSecretKeyLength(): int
    {
        return \SODIUM_CRYPTO_BOX_SECRETKEYBYTES;
    }

    public static function isEncryptionKeyLength(string $key): bool
    {
        return KeyLength::isLengthEqual($key, KeyLength::getEncryptionKeyLength());
    }

    public static function isPublicKeyLength(string $key): bool
    {
        return KeyLength::isLengthEqual($key, KeyLength::getPublicKeyLength());
    }

    public static function isSaltLength(string $key): bool
    {
        return KeyLength::isLengthEqual($key, KeyLength::getSaltLength());
    }

    public static function isSecretKeyLength(string $key): bool
    {
        return KeyLength::isLengthEqual($key, KeyLength::getSecretKeyLength());
    }

    private static function isLengthEqual(string $key, int $length): bool
    {
        return Binary::safeStrlen($key) === $length;
    }
}
