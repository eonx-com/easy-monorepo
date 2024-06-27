<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Encryptor;

use EonX\EasyEncryption\Common\ValueObject\DecryptedStringInterface;

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
        null|array|string $key = null,
    ): string;

    /**
     * Returns a base64 encoded json string containing the key name, and the encrypted string.
     */
    public function encrypt(string $text, ?string $keyName = null): string;

    public function encryptRaw(
        string $text,
        null|array|string $key = null,
    ): string;
}
