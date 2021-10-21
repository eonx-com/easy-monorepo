<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface EncryptionKeyProviderInterface
{
    /**
     * @param string $keyName
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function getKey(string $keyName);

    public function hasKey(string $keyName): bool;

    /**
     * Clear local cache of resolved encryption keys.
     */
    public function reset(): void;
}
