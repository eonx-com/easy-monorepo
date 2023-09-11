<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptorInterface
{
    public const DEFAULT_KEY_NAME = 'app';

    public const ENCRYPTED_KEY_NAME = 'keyName';

    public const ENCRYPTED_KEY_VALUE = 'value';

    /**
     * Accepts a base64 encoded json string containing the key name, and the encrypted string.
     */
    public function decrypt(string $text): DecryptedStringInterface;

    public function decryptRaw(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key = null,
    ): string;

    /**
     * Returns a base64 encoded json string containing the key name, and the encrypted string.
     */
    public function encrypt(string $text, ?string $keyName = null): string;

    public function encryptRaw(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key = null,
    ): string;
}
