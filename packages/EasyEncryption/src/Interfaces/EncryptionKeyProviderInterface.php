<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptionKeyProviderInterface
{
    public function getKey(string $keyName): EncryptionKey|EncryptionKeyPair;

    public function hasKey(string $keyName): bool;

    /**
     * Clear local cache of resolved encryption keys.
     */
    public function reset(): void;
}
