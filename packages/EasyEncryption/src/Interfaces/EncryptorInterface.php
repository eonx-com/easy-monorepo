<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface EncryptorInterface
{
    /**
     * @var string
     */
    public const DEFAULT_KEY_NAME = 'app';

    /**
     * @var string
     */
    public const ENCRYPTED_KEY_NAME = 'keyName';

    /**
     * @var string
     */
    public const ENCRYPTED_KEY_VALUE = 'value';

    /**
     * Accepts a base64 encoded json string containing the key name, and the encrypted string.
     */
    public function decrypt(string $text): DecryptedStringInterface;

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    public function decryptRaw(
        string $text,
        null|array|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key = null
    ): string;

    /**
     * Returns a base64 encoded json string containing the key name, and the encrypted string.
     */
    public function encrypt(string $text, ?string $keyName = null): string;

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    public function encryptRaw(
        string $text,
        null|array|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key = null
    ): string;
}
